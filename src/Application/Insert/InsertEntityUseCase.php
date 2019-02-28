<?php

namespace Osds\Backoffice\Application\Insert;

use Osds\Backoffice\UI\Helpers\Request;

final class InsertEntityUseCase
{

    public function __construct()
    {
    }

    public function execute($entity, $requestParameters)
    {

        $request = new Request($entity, 'insert', $requestParameters);
        $response = $request->sendRequest();
        return $response;

    }
}
