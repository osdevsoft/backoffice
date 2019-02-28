<?php

namespace Osds\Backoffice\UI;

use Osds\Backoffice\UI\Helpers\UpsertCallbacks;
use Osds\Backoffice\UI\Helpers\View;
use Osds\Backoffice\UI\Helpers\Session;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;




/**
 * @Route("/backoffice")
 */
class BaseUIController
{

    use UpsertCallbacks;
    use View;
    
    const VAR_SESSION_NAME = 'backoffice_user_logged';

    const PAGES = [
        'session' => [
            'login' => '/session/login',
            'logout' => '/session/logout'
        ]
    ];

    public $request;



    public $config;

    public $models;



    public $session;

    public $vendor_path = __DIR__ . '/../';

    public $commands_path = '\Osds\Backoffice\Application\Commands\%action%%model%Command';

    /**
     *
     * @Route(
     *     "",
     *     methods={"GET"}
     * )
     */
    public function __construct()
    {
        #console request
        if (!isset($_SERVER['REQUEST_URI'])) {
            return true;
        }
        try {
            $this->session = new Session();

            if (#is not in Backoffice login page
                !strstr($_SERVER['REQUEST_URI'], 'login')
                #user is not logged
                && !self::checkAuth($this->session)
            ) {
                //$this->redirect(self::PAGES['session']['login']);
            }

            $this->request = new \stdClass();
            $this->request->parameters = (isset($_REQUEST))?$_REQUEST:null;

            if (!empty($_FILES)) {
                $this->request->files = $_FILES;
            }

            $this->loadSiteConfiguration();

            $this->models = $this->config['domain_structure']['models'];
            
        } catch (\Exception $e) {
            dd($e->getMessage());
        }

    }

    public function build()
    {
        $this->request = new \stdClass();
        $this->request->parameters = (isset($_REQUEST))?$_REQUEST:null;

        if (!empty($_FILES)) {
            $this->request->files = $_FILES;
        }
    }

    public static function checkAuth($session)
    {
        $backoffice_token = $session->get(self::VAR_SESSION_NAME);

        if (!
        ($backoffice_token != null
            && self::isValidToken($backoffice_token)
        )) {
            return false;
        }

        return true;
    }



    public static function isValidToken($backoffice_token)
    {
        return true;
    }

    /**
     *
     * Get all the contents of the referenced Models (foreign-keyed models on DB)
     *
     * @return array
     */
    private function getReferencedContents($schema_info = null, $model = null)
    {
        if($schema_info == null)
        {
            $schema_info_request = $this->performAction('getSchema');
            $schema_info = $schema_info_request['items'][0]['fields'];
        }

        $referenced_contents = [];

        return $referenced_contents;
    }

    private function preTreatBeforeSaving($model)
    {
        #treat field before saving it
        if(isset($this->config['domain_structure']['models'][$model]['fields']['fields_schema']))
        {
            foreach($this->config['domain_structure']['models'][$model]['fields']['fields_schema'] as $field => $field_schema)
            {
                #this field has callbacks, call them
                if(isset($this->request_data['post'][$field]) && isset($field_schema['callbacks']))
                {
                    $field_value = $this->request_data['post'][$field];
                    foreach($field_schema['callbacks'] as $callback)
                    {
                        if($this->isMultilanguageField($field_value))
                        {
                            foreach($field_value as $lang => $value)
                            {
                                $field_value[$lang] = $this->{$callback}($field_value[$lang]);
                            }
                        } else {
                            $field_value = $this->{$callback}($field_value);
                        }
                    }
                    $this->request_data['post'][$field] = $field_value;
                }
            }
        }


        #if model has user_id field, fill it with session_id
        if(
            isset($this->config['domain_structure']['models'][$model]['schema']['by_user'])
            && $this->config['domain_structure']['models'][$model]['schema']['by_user'] == true
        )
        {
            $session_data = $this->session->get(BaseUIController::var_session_name);
            $this->request_data['post']['user_id'] = $session_data['id'];
        }

        #if it's multilanguage, json encode its values
        if(isset($this->config['domain_structure']['languages']))
        {
            foreach($this->request_data['post'] as $field => $value)
            {
                if($this->isMultilanguageField($value))
                {
                    $this->request_data['post'][$field] = json_encode($value);
                }
            }
        }

        #set to null empty values to avoid casting errors with the db
        foreach($this->request_data['post'] as $field => &$value)
        {
            if($value == '') $value = 'DB_NULL';
        }
    }

    private function preTreatDataBeforeDisplaying($model, $data, $localize = false)
    {

        if (@count($data['items']) > 0) {

            #treat multilanguage fields
            if (isset($this->config['domain_structure']['languages'])
                && isset($this->config['domain_structure']['models'][$model]['schema']['multilanguage_fields'])
            ) {
                foreach ($data['items'] as &$item) {
                    foreach ($this->config['domain_structure']['models'][$model]['schema']['multilanguage_fields'] as $ml_field) {
                        $item[$ml_field] = json_decode($item[$ml_field], true);
                        #preserve only a desired language
                        if ($localize
                            && is_array($item[$ml_field])
                        ) {
                            #check if we have at least one item of the array that is a valid language
                            if (isset($this->visitor_language)
                                && count(array_intersect(array_keys($item[$ml_field]), $this->config['domain_structure']['languages'])) > 0
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