<?php

namespace Osds\Backoffice\Application\Commands;

use Osds\Backoffice\Application\Helpers\Request;

class BaseModelCommand
{

    /**
     * define HTTP methods that API will use on the routes
     */
    public $actions_methods = [
        'list' => 'get',
        'create' => 'post',
        'update' => 'post', #it does send the ID through post. Laravel is not able to implement PUT http method :|
        'delete' => 'delete',
        'getschema' => 'get',
        'getmetadata' => 'get',
    ];

    /**
     * @var $model_path : Class of the Model. If doesn't exist on namespace \Backoffice\Domain\Models\, uses BaseModel
     * it is used in case a custom command needs to perform some action modifiying a model
     */
    public $model_path = '\Osds\Backoffice\Domain\Models\BaseModel';
    /**
     * @var $model_name : Name of the model (entity on API)
     */
    public $model_name = '';

    /**
     * BaseCommand constructor.
     * @param String $package : possible name of the package to use
     * @param String|null $action
     */
    public function __construct(String $package = null, String $action = null)
    {

        $this->model_name = ucfirst(strtolower($package));
        #if package is not the default one, try to use its own Domain Model
        if ($package != 'Model') {
            $model_path = '\Osds\Backoffice\Domain\Models\\' . $this->model_name . 'Model';
            if (class_exists($model_path)) {
                $this->model_path = $model_path;
            }
        } else {
            // need to get model name (the url one, laravel prefix) from route
            #TODO
            $this->model_name = 'user';
        }

        $this->request_base_url = $this->model_name;

        $this->request_action = $action;
    }

    public function execute($data)
    {

        $api_request = new Request($this->request_base_url, $this->actions_methods[$this->request_action], $data);

        $response = $api_request->sendRequest();

        return $response;
    }
}
