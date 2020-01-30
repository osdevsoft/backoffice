<?php

namespace Osds\Backoffice\Application\Insert;

use Osds\Backoffice\Domain\Bus\Command\CommandHandler;

final class InsertEntityCommandHandler implements CommandHandler
{
    private $useCase;

    public function __construct(InsertEntityUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function handle(InsertEntityCommand $command)
    {
        return $this->useCase->execute(
            $command->entity(),
            $command->requestParameters()
            );
    }
}
