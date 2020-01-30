<?php

namespace Osds\Backoffice\Application\Commands;

use Osds\Backoffice\Application\Helpers\Request;

class GetmetadataModelCommand extends BaseModelCommand
{

    /**
     * Executes the Create Command for this generic Model
     *
     * @param $data
     * @return mixed
     */
    public function execute($data)
    {
        $url = $this->request_base_url . '/getMetadata';
        $api_request = new Request($url, 'get', $data);
        $response = $api_request->sendRequest();
        return $response;

    }


}