<?php

namespace Osds\Backoffice\Application\Update;

use Osds\Backoffice\Domain\Bus\Command\Command;

final class UpdateEntityCommand implements Command
{

    private $entity;
    private $uuid;
    private $requestParameters;

    public function __construct(
        string $entity,
        string $uuid,
        $requestParameters
    )
    {
        $this->entity = $entity;
        $this->uuid = $uuid;
        $this->requestParameters = $requestParameters;
    }

    public function entity(): string
    {
        return $this->entity;
    }

    public function uuid(): string
    {
        return $this->uuid;
    }

    public function requestParameters():? array
    {
        return [
            'post' => $this->requestParameters,
            'uri' => [$this->uuid]
            ];
    }

}