<?php

namespace Osds\Backoffice\Application\Update;

use Osds\Backoffice\Domain\Bus\Query\Command;

final class UpdateEntityCommand implements Command
{

    private $entity;

    private $requestParameters;

    public function __construct(
        string $entity,
        Array $requestParameters
    )
    {
        $this->entity = $entity;
        $this->requestParameters = $request_parameters;
    }

    public function entity(): string
    {
        return $this->entity;
    }

    public function requestParameters(): array
    {
        return $this->requestParameters;
    }

}