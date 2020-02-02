<?php

namespace Osds\Backoffice\UI;


use Osds\Auth\Infrastructure\UI\StaticClass\Auth;
use Osds\DDDCommon\Infrastructure\Persistence\SessionRepository;
use Osds\DDDCommon\Infrastructure\View\ViewInterface;
use Osds\Backoffice\Application\Localization\LoadLocalizationApplication;

use Osds\Backoffice\UI\Helpers\UpsertCallbacks;

use Symfony\Component\Routing\Annotation\Route;

use Osds\DDDCommon\Infrastructure\Helpers\Language;
use Osds\DDDCommon\Infrastructure\Helpers\UI;

use Osds\Backoffice\Infrastructure\Tools;

class BaseUIController
{

    use UpsertCallbacks;
            
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

            $userData = null;

            if (#is not in Backoffice login page
                !strstr($_SERVER['REQUEST_URI'], 'login') &&
                #user auth
                ($userData = self::checkUserAuth()) == null
            ) {
                UI::redirect(self::PAGES['session']['login']);
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

        $this->config['backoffice'] = Tools::loadSiteConfiguration();
        $this->entities = $this->config['backoffice']['entities'];
    }
    
    public static function checkUserAuth()
    {
        return Auth::getUserAuthToken();
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
                        if(Language::isMultilanguageField($field_value, $this->config['backoffice']['languages']))
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
                if(Language::isMultilanguageField($value, $this->config['backoffice']['languages']))
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

    protected function getReferencedEntitiesToRequest($entity, $config)
    {
        $requestParameters = [];
        if (isset($config)
            && isset($config['backoffice'])
            && isset($config['backoffice']['entities'][$entity])
            && isset($config['backoffice']['entities'][$entity]['fields'])
            && isset($config['backoffice']['entities'][$entity]['fields']['in_detail'])
        ) {
            #we have referenced fields to display => we have to join them to recover them
            foreach ($config['backoffice']['entities'][$entity]['fields']['in_detail'] as $detailField) {
                if (strstr($detailField, '.')) {
                    $gatherEntities[] = preg_replace('/\.[^\.]*$/', '', $detailField);
                }
            }
            if (isset($gatherEntities)) {
                $requestParameters['get']['referenced_entities'] = implode(',', $gatherEntities);
                $requestParameters['get']['referenced_entities_contents'] = implode(',', $gatherEntities);
            }
        }
        return $requestParameters;
    }

}