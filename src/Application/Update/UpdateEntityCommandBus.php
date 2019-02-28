<?php

namespace Osds\Backoffice\Application\Update;

use Osds\Backoffice\Domain\Bus\Command\Command;
use Osds\Backoffice\Domain\Bus\Command\CommandBus;

class UpdateEntityCommandBus implements CommandBus
{

    private $commandHandler;

    public function __construct(UpdateEntityCommandHandler $commandHandler)
    {
        $this->commandHandler = $commandHandler;
    }

    public function dispatch(Command $command)
    {
        return $this->commandHandler->handle($command);
    }

}