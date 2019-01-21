<?php

namespace Osds\Backoffice\Infrastructure\Controllers;

use Osds\Backoffice\Application\Helpers\Session;
use Osds\Backoffice\Application\Traits\ActionsTrait;
use Osds\Backoffice\Application\Traits\CallbacksTrait;
use Osds\Backoffice\Application\Traits\ViewTrait;
use Osds\Backoffice\Application\Traits\UtilsTrait;
use Osds\Backoffice\Application\Traits\LocalizationTrait;

use Symfony\Component\HttpFoundation\Request;

class BaseController
{

    use ActionsTrait;
    use CallbacksTrait;
    use UtilsTrait;
    use ViewTrait;
    use LocalizationTrait;


    const PAGES = [
        'session' => [
            'login' => '/session/login',
            'logout' => '/session/logout'
        ]
    ];

    public $config;

    public $models;

    public $request_data;

    public $session;

    public $vendor_path = __DIR__ . '/../../';

    public $commands_path = '\Osds\Backoffice\Application\Commands\%action%%model%Command';

    public function __construct(Request $request = null)
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
                && !LoginController::checkAuth($this->session)
            ) {
                $this->redirect(self::PAGES['session']['login']);
            }

            $this->loadSiteConfiguration();
        } catch (\Exception $e) {
            dd($e->getMessage());
        }

        #models to use in the view
        $this->models = $this->config['domain_structure']['models'];

        $this->request_data = [];
        $this->request_data['post'] = $_POST;
        $this->request_data['get'] = $_GET;

        if (isset($_FILES)) {
            foreach ($_FILES as $field => $file) {
                if (!empty($file['tmp_name'])) {
                    #a file has been sent
                    $persistence = null;
                    if (isset($this->config['domain_structure']['persistence'][$field])) {
                        $persistence = $this->config['domain_structure']['persistence'][$field];
                    }
                    $this->request_data['multipart'][$field] = [
                        'persistence' => $persistence,
                        'name' => $file['name'],
                        'content' => file_get_contents($file['tmp_name'])
                    ];
                }
            }
        }
    }

    #performs a requested Backoffice action (list, view, create, delete...)
    protected function performAction($action, $model = null)
    {
        if ($model == null) {
            #by default, set the base one
            $model = 'Model';

            #if the Controller calling this method is not the base one
            #try to get the command for the called action and model
            if (!strstr(get_called_class(), 'BackofficeController')) {
                $model = str_replace('Controller', '', get_called_class());
                $model = preg_replace('/.*\\\([a-z]*)$/i', '$1', $model);
            }
        }
        $commandLocation = $this->getCommandPath($action, $model);

        #TODO: what to do if no command exist for this action?
        $data = $this->request_data;
        #execute command for this action and model
        $command = new $commandLocation($model, $action);
        return $command->execute($data);
    }

    /**
     * Gets the path of the requested command. This is quite obvious, but needed to handle the packages Commands
     *
     * @param $action
     * @param $model
     * @return mixed|string
     */
    private function getCommandPath($action, $model)
    {
        $command_path = $this->commands_path;
        $keys = ['action', 'model'];
        foreach ($keys as $key) {
            $command_path = str_replace("%{$key}%", ucfirst(strtolower(${$key})), $command_path);
        }
        #required command doesn't exist, use the base one
        if (!class_exists($command_path)) {
            if ($model != 'Model') {
                $command_path = $this->getCommandPath($action, 'Model');
            } else {
                #use BaseCommand (it implements __call method)
                $command_path = $this->getCommandPath('Base', 'Model');
            }
        }

        return $command_path;
    }
}
