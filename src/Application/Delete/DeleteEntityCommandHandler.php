<?php

namespace Osds\Backoffice\Application\Delete;

use Osds\Backoffice\Domain\Bus\Command\CommandHandler;

final class DeleteEntityCommandHandler implements CommandHandler
{
    private $useCase;

    public function __construct(DeleteEntityUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function handle(DeleteEntityCommand $command)
    {
        return $this->useCase->execute(
            $command->entity(),
            $command->requestParameters()
            );
    }
}
