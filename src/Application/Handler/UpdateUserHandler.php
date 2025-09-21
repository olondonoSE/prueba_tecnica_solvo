<?php

namespace App\Application\Handler;

use App\Application\Command\UpdateUserCommand;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Model\User;
use App\Domain\Model\Role;
use App\Domain\Model\State;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UpdateUserHandler
{
    public function __construct(
        private UserRepositoryInterface $repo,
        private EntityManagerInterface $em
        ) {}

    public function __invoke(UpdateUserCommand $command): void
    {
        $user = $this->repo->findById($command->id);
        if (!$user) {
            throw new \RuntimeException("Usuario no encontrado");
        }

        // Buscar las entidades de Doctrine primero
        $roleEntity = $this->em->getRepository(\App\Infrastructure\Persistence\Doctrine\Entities\RoleEntity::class)
            ->find($command->roleId);

        $stateEntity = $this->em->getRepository(\App\Infrastructure\Persistence\Doctrine\Entities\StateEntity::class)
            ->find($command->stateId);

        if (!$roleEntity) {
            throw new \InvalidArgumentException("Role con ID {$command->roleId} no encontrado");
        }

        if (!$stateEntity) {
            throw new \InvalidArgumentException("State con ID {$command->stateId} no encontrado");
        }

        // Crear objetos de DOMINIO (no entidades)
        $role = new Role($roleEntity->getId(), $roleEntity->getName());
        $state = new State($stateEntity->getId(), $stateEntity->getName());

        $user->setName($command->name);
        $user->setEmail($command->email);
        $user->setDepartment($command->department);
        $user->setRole($role);
        $user->setState($state);

        $this->repo->save($user);
    }
}
