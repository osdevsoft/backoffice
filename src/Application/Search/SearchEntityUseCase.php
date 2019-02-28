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
        $request_parameters = ['search_fields' => $requestParameters];

        $request = new Request($entity, 'get', $request_parameters);
        $response = $request->sendRequest();
        return $response;

    }
}
