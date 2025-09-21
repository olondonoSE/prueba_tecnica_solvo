<?php

namespace App\Application\Handler;

use App\Application\Command\DeleteUserCommand;
use App\Domain\Repository\UserRepositoryInterface;

class DeleteUserHandler
{
    public function __construct(private UserRepositoryInterface $repo) {}

    public function __invoke(DeleteUserCommand $command): void
    {
        $user = $this->repo->findById($command->id);
        if (!$user) {
            throw new \RuntimeException("Usuario no encontrado");
        }

        $this->repo->delete($command->id);
    }
}
