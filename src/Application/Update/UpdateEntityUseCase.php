<?php

namespace Osds\Backoffice\Application\Update;

use Osds\Backoffice\UI\Helpers\Request;

final class UpdateEntityUseCase
{

    public function __construct()
    {
    }

    public function execute($entity, $requestParameters)
    {

        $request = new Request($entity, 'update', $requestParameters);
        $response = $request->sendRequest();
        return $response;

    }
}
