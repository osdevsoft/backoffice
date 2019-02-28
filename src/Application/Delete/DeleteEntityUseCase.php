<?php

namespace Osds\Backoffice\Application\Delete;

use Osds\Backoffice\UI\Helpers\Request;

final class DeleteEntityUseCase
{

    public function __construct()
    {
    }

    public function execute($entity, $requestParameters)
    {

        $request = new Request($entity, 'delete', $requestParameters);
        $response = $request->sendRequest();
        return $response;

    }
}
