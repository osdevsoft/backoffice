<?php

namespace Osds\Backoffice\Domain\Bus\Command;

interface CommandBus
{
    public function dispatch(Command $command);
}
