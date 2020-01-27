<?php

namespace Osds\Backoffice\Application\Update;

use Osds\Backoffice\Domain\Bus\Command\CommandHandler;

final class UpdateEntityCommandHandler implements CommandHandler
{
    private $useCase;

    public function __construct(UpdateEntityUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function handle(UpdateEntityCommand $command)
    {
        return $this->useCase->execute(
            $command->entity(),
            $command->requestParameters()
            );
    }
}
