<?php

namespace Osds\Backoffice\Application\Search;

use Osds\Backoffice\Domain\Bus\Query\Query;
use Osds\Backoffice\Domain\Bus\Query\QueryBus;

class SearchEntityQueryBus implements QueryBus
{

    private $query_handler;

    public function __construct(SearchEntityQueryHandler $query_handler)
    {
        $this->query_handler = $query_handler;
    }

    public function ask(Query $query)
    {
        return $this->query_handler->handle($query);
    }

}