<?php

namespace Osds\Backoffice\Infrastructure\Controllers;

class BackofficeController extends BaseController
{

    public $user = null;

    public $models_metadata = null;

    public function index()
    {
        $model = array_keys($this->config['domain_structure']['models'])[0];
        $this->redirect("/{$model}");
    }

}