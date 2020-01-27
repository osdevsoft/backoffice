<?php

namespace Osds\Backoffice\UI;

use Osds\DDDCommon\Infrastructure\Persistence\SessionRepository;
use Osds\DDDCommon\Infrastructure\View\ViewInterface;
use Osds\Backoffice\Application\Localization\LoadLocalizationApplication;

use Osds\Backoffice\UI\Helpers\UpsertCallbacks;

use Symfony\Component\Routing\Annotation\Route;

use function Osds\Backoffice\Utils\loadSiteConfiguration;
use function Osds\Backoffice\Utils\isMultilanguageField;


class BaseUIController
{

    use UpsertCallbacks;
            
    const USER_AUTH_COOKIE = 'bo_user_auth';
    const SERVICE_AUTH_COOKIE = 'bo_service_auth';
    
    const PAGES = [
        'session' => [
            'login' => '/session/login',
            'logout' => '/session/logout'
        ]
    ];

    protected $session;
    protected $view;
    protected $loadLocalizationApplication;

    public $request;

    public $config;

    public $models;


    public $vendor_path = __DIR__ . '/../';

    public $commands_path = '\Osds\Backoffice\Application\Commands\%action%%model%Command';


    public function __construct(
        SessionRepository $session,
        ViewInterface $view,
        LoadLocalizationApplication $loadLocalizationApplication
    )
    {
        #console request
        #if (!isset($_SERVER['REQUEST_URI'])) {
        #    return true;
        #}
        try {
            $this->view = $view;
            $this->session = $session;
            $this->loadLocalizationApplication = $loadLocalizationApplication;

            $this->view->setVariable('locale', $this->loadLocalizationApplication->execute());

            #Auth BO - API
            self::checkServiceAuth($this->session);

            if (#is not in Backoffice login page
                !strstr($_SERVER['REQUEST_URI'], 'login')
                #user auth
                && ($userData = self::checkUserAuth($this->session)) == false
            ) {
                $this->redirect(self::PAGES['session']['login']);
            }
            $this->view->setVariable('loggedUser', $userData);

            $this->build();

        } catch (\Exception $e) {
            dd($e->getMessage());
        }

    }

    public function build()
    {
        $this->request = new \stdClass();
        $this->request->parameters =[];
        $this->request->parameters['get'] = (isset($_GET))?$_GET:null;
        $this->request->parameters['post'] = (isset($_POST))?$_POST:null;

        $this->request->parameters['uri'] = null;

        if (!empty($_FILES)) {
            $this->request->files = $_FILES;
        }

        $this->config['backoffice'] = loadSiteConfiguration();
        $this->entities = $this->config['backoffice']['entities'];
    }
    
    public static function checkServiceAuth($session)
    {
        
        $serviceToken = $session->find(self::SERVICE_AUTH_COOKIE);

        if ($serviceToken == null) {
            #TODO: request API for a token
            $serviceToken = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE1ODAwMzQyODUsImV4cCI6MTU4MDYzOTA4NSwicm9sZXMiOlsiQWRtaW4iLCJBUEkiXSwidXNlcm5hbWUiOiJ4YXZpY3gifQ.rlJAbNsNp1r9WVLnqbEuyzMG8esZBjI36E7CQSRea6WofVddLSUEgKz4lO5r79J22oM4kztzbxiNEefD7dvNhivmiACW881qihbn1aP9kWTRSSEZL1Ii3bDHNSgO4t5xd_Olp-GmmFH2zUxgc1sRFrDStTgYnCNLx1-aYKtvOUruS1f2yq3M2F4Jzaddur_fJ_M-YuBbhAQecJwzALh1pnyI3_HOwwFHGbraRJK6afQVnfqoNv9HmHSB-kb78z9XZ7uiAkLG0v1UbnRLlnFiGk2dSXaGrjVtHSnvWq6IuLqyZK-GfR80nvK2HAnPka0b1vKOCBVle5vpMc9wIuPG1knTTeeSoqy3U725TFLCI30Ys6aMEzDTxzKE7lHmXsLzVRuj8AyrW6CXrnIM42CNaY3jqR7dkUI_MQge7A84oqvLI7QjFrLlNPz3B8pb3nr-6nKuS3gpEkDvjTODbvTvyIfPX7ETmTjYS1Vlo9DsOtDz_kE8khaFuKgO-U1NZoNhamEREWG9ExxxbhAOL1fclMDGEbos8xpH9QqN0kfY6RGwPWmzdsyvfdhQxEoXLIJszMASPopMnk_0Z5qvxZNMMvlKfyS8qu43TuiwxPIahBbQBaKp0rMVHXxxzera7t2Ci89jh6qNgWgGBkjL1CYni2KkwEVoAeEqqX1NLEooRcE";
            $session->insert(self::SERVICE_AUTH_COOKIE, $serviceToken);
        }
    }

    public static function checkUserAuth($session)
    {
        $loggedUser = $session->find(self::USER_AUTH_COOKIE);
        if ($loggedUser == null) {
            return false;
        }

        return $loggedUser;
    }

    /**
     *
     * Get all the contents of the referenced Entities (foreign-keyed models on DB)
     *
     * @return array
     */
    protected function getReferencedContents($schema_info = null, $model = null)
    {
        if($schema_info == null)
        {
            $schema_info_request = $this->performAction('getSchema');
            $schema_info = $schema_info_request['items'][0]['fields'];
        }

        $referenced_contents = [];

        return $referenced_contents;
    }

    protected function preTreatBeforeSaving($entity, $requestParameters)
    {
        #treat field before saving it
        if(isset($this->entities[$entity]['fields']['fields_schema']))
        {
            foreach($this->entities[$entity]['fields']['fields_schema'] as $field => $field_schema)
            {
                #this field has callbacks, call them
                if(isset($requestParameters[$field]) && isset($field_schema['callbacks']))
                {
                    $field_value = $requestParameters[$field];
                    foreach($field_schema['callbacks'] as $callback)
                    {
                        if(isMultilanguageField($field_value, $this->config['backoffice']['languages']))
                        {
                            foreach($field_value as $lang => $value)
                            {
                                $field_value[$lang] = $this->{$callback}($field_value[$lang]);
                            }
                        } else {
                            $field_value = $this->{$callback}($field_value);
                        }
                    }
                    $requestParameters[$field] = $field_value;
                }
            }
        }


        #if model has user_id field, fill it with session_id
        if(
            isset($this->entities[$entity]['schema']['by_user'])
            && $this->entities[$entity]['schema']['by_user'] == true
        )
        {
            $session_data = $this->session->find(BaseUIController::USER_AUTH_COOKIE);
            $requestParameters['user_uuid'] = $session_data['uuid'];
        }

        #if it's multilanguage, json encode its values
        if(isset($this->config['backoffice']['languages']))
        {
            foreach($requestParameters as $field => $value)
            {
                if(isMultilanguageField($value, $this->config['backoffice']['languages']))
                {
                    $requestParameters[$field] = json_encode($value);
                }
            }
        }

        #set to null empty values to avoid casting errors with the db
        foreach($requestParameters as $field => &$value)
        {
//            if($value == '') $value = NULL;
        }

        return $requestParameters;
    }

    protected function preTreatDataBeforeDisplaying($entity, $data, $localize = false)
    {

        if (@count($data['items']) > 0) {

            #treat multilanguage fields
            if (isset($this->config['backoffice']['languages'])
                && isset($this->config['backoffice']['entities'][$entity]['schema']['multilanguage_fields'])
            ) {
                foreach ($data['items'] as &$item) {
                    foreach ($this->config['backoffice']['entities'][$entity]['schema']['multilanguage_fields'] as $ml_field) {
                        $item[$ml_field] = json_decode($item[$ml_field], true);
                        #preserve only a desired language
                        if ($localize
                            && is_array($item[$ml_field])
                        ) {
                            #check if we have at least one item of the array that is a valid language
                            if (isset($this->visitor_language)
                                && count(array_intersect(array_keys($item[$ml_field]), $this->config['backoffice']['languages'])) > 0
                                && in_array($this->visitor_language, array_keys($item[$ml_field]))
                            ) {
                                #visitor language has a defined value on the field array
                                $item[$ml_field] = $item[$ml_field][$this->visitor_language];
                            } else {
                                #user language is not defined, use first
                                $item[$ml_field] = current($item[$ml_field]);
                            }
                        }
                    }
                }
            }
        }

        return $data;
    }

}