<?php

namespace Osds\Backoffice\Application\Delete;

use Osds\DDDCommon\Infrastructure\Communication\OutputRequest;

final class DeleteEntityUseCase
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

        $this->outputRequest->setQuery($entity, 'delete', $requestParameters);
        $response = $this->outputRequest->sendRequest('backoffice');

        return $response;

    }

}
