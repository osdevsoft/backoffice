<?php

namespace Osds\Backoffice\Application\Insert;

use Osds\Backoffice\Domain\Bus\Command\Command;
use Osds\Backoffice\Domain\Bus\Command\CommandBus;

class InsertEntityCommandBus implements CommandBus
{

    private $commandHandler;

    public function __construct(InsertEntityCommandHandler $commandHandler)
    {
        $this->commandHandler = $commandHandler;
    }

    public function dispatch(Command $command)
    {
        return $this->commandHandler->handle($command);
    }

}