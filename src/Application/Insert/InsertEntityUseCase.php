<?php

namespace Osds\Backoffice\Application\Insert;

use Osds\DDDCommon\Infrastructure\Communication\OutputRequest;

class InsertEntityUseCase
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

        $this->outputRequest->setQuery($entity, 'post', $requestParameters);
        $response = $this->outputRequest->sendRequest('backoffice');

        return $response;

    }
}
