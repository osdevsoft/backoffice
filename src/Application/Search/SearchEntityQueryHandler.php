<?php

namespace Osds\Backoffice\Application\Search;

use Osds\Backoffice\Domain\Bus\Query\QueryHandler;

final class SearchEntityQueryHandler implements QueryHandler
{
    private $useCase;

    public function __construct(SearchEntityUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function handle(SearchEntityQuery $query)
    {
        return $this->useCase->execute(
            $query->entity(),
            $query->requestParameters()
            );
    }
}
