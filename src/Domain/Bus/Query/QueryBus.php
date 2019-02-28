<?php

namespace Osds\Backoffice\Domain\Bus\Query;

interface QueryBus
{
    public function ask(Query $query);
}
