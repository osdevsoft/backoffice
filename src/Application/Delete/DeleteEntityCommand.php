<?php

namespace Osds\Backoffice\Application\Delete;

use Osds\Backoffice\Domain\Bus\Command\Command;

final class DeleteEntityCommand implements Command
{

    private $entity;

    private $requestParameters;

    public function __construct(
        string $entity,
        Array $requestParameters
    )
    {
        $this->entity = $entity;
        $this->requestParameters = $requestParameters;
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