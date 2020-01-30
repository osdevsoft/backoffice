<?php

namespace Osds\Backoffice\Application\Commands;

use Osds\Backoffice\Application\Helpers\Request;

class GetschemaModelCommand extends BaseModelCommand
{

    /**
     * Executes the Create Command for this generic Model
     *
     * @param $data
     * @return mixed
     */
    public function execute($data)
    {
        $url = $this->request_base_url . '/schema';
        $api_request = new Request($url, 'get', $data);
        $response = $api_request->sendRequest();
        return $response;

    }


}