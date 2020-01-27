<?php

namespace Osds\Backoffice\Application\Search;

use Osds\Backoffice\Domain\Bus\Query\Query;

final class SearchEntityQuery implements Query
{

    private $entity;

    private $request_parameters;

    public function __construct(
        string $entity,
        array $request_parameters
    )
    {
        $this->entity = $entity;
        $this->request_parameters = $request_parameters;
    }

    public function entity(): string
    {
        return $this->entity;
    }

    public function requestParameters(): array
    {
        return $this->request_parameters;
    }

}