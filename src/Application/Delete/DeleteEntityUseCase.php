<?php

namespace Osds\Backoffice\Application\Delete;

use Osds\DDDCommon\Infrastructure\Communication\OutputRequest;
use Osds\DDDCommon\Infrastructure\Persistence\SessionRepository;

use Osds\Backoffice\UI\BaseUIController;

final class DeleteEntityUseCase
{

    public function __construct(
        OutputRequest $outputRequest,
        SessionRepository $session
    )
    {
        $this->outputRequest = $outputRequest;
        $this->session = $session;
    }

    public function execute($entity, $requestParameters)
    {

        $this->outputRequest->setQuery($entity, 'delete', $requestParameters);
        $this->outputRequest->addAuthToken($this->session->find(BaseUIController::SERVICE_AUTH_COOKIE));
        $response = $this->outputRequest->sendRequest();

        return $response;

    }

}
