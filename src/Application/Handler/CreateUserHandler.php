<?php

namespace App\Application\Handler;

use App\Application\Command\CreateUserCommand;
use App\Domain\Model\User;
use App\Domain\Model\Role;
use App\Domain\Model\State;
use App\Domain\Repository\UserRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CreateUserHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepo,
        private EntityManagerInterface $em
    ) {}

    public function __invoke(CreateUserCommand $command): void
    {
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

        // Crear User de DOMINIO
        $user = new User(
            null, // ID se generará automáticamente
            $command->name,
            $command->email,
            $command->department,
            $role,
            $state
        );

        // Guardar el objeto de dominio (el repositorio se encarga del mapeo)
        $this->userRepo->save($user);
    }
}
