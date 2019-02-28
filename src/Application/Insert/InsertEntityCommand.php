<?php

namespace Osds\Backoffice\Application\Insert;

use Osds\Backoffice\Domain\Bus\Query\Command;

final class InsertEntityCommand implements Command
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