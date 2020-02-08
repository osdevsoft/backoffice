<?php

namespace Osds\Backoffice\Application\Update;

use Osds\DDDCommon\Infrastructure\Communication\OutputRequest;

class UpdateEntityUseCase
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

        $this->outputRequest->setQuery($entity,'post', $requestParameters);
        $response = $this->outputRequest->sendRequest('backoffice');

        #TODO: make generic
        if($entity == 'static_page') {
            $entityCustomUseCase = 'Osds\Backoffice\Application\Update\StaticPage\UpdateStaticPageUseCase';
            if(class_exists($entityCustomUseCase)) {
                $entityCustomUseCase = new $entityCustomUseCase;
                $entityCustomUseCase->execute($requestParameters);
            }
        }

        return $response;

    }
}
