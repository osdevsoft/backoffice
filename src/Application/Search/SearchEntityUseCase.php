<?php

namespace Osds\Backoffice\Application\Search;

use Osds\Backoffice\UI\Helpers\Request;

final class SearchEntityUseCase
{

    public function __construct()
    {
    }

    public function execute($entity, $requestParameters)
    {

        $request = new Request($entity, 'get', $requestParameters);
        $response = $request->sendRequest();

        return $response;

    }
}
