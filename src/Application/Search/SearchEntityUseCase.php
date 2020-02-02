<?php

namespace Osds\Backoffice\Application\Search;

use Osds\DDDCommon\Infrastructure\Communication\OutputRequest;

final class SearchEntityUseCase
{

    private $outputRequest;

    public function __construct(
        OutputRequest $outputRequest
    )
    {
        $this->outputRequest = $outputRequest;
    }

    public function execute($entity, $requestParameters)
    {

        $this->outputRequest->setQuery($entity, 'get', $requestParameters);
        $response = $this->outputRequest->sendRequest();

        return $response;

    }
}
