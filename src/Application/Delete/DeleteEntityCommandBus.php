<?php

namespace Osds\Backoffice\Application\Delete;

use Osds\Backoffice\Domain\Bus\Command\Command;
use Osds\Backoffice\Domain\Bus\Command\CommandBus;

class DeleteEntityCommandBus implements CommandBus
{

    private $commandHandler;

    public function __construct(DeleteEntityCommandHandler $commandHandler)
    {
        $this->commandHandler = $commandHandler;
    }

    public function dispatch(Command $command)
    {
        return $this->commandHandler->handle($command);
    }

}