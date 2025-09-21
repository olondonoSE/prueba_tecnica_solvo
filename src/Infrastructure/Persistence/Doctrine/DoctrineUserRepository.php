<?php

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Model\User;
use App\Domain\Model\Role;
use App\Domain\Model\State;
use Doctrine\ORM\EntityManagerInterface;
use App\Infrastructure\Persistence\Doctrine\Entities\UserEntity;
use App\Infrastructure\Persistence\Doctrine\Entities\RoleEntity;
use App\Infrastructure\Persistence\Doctrine\Entities\StateEntity;

class DoctrineUserRepository implements UserRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    public function save(User $user): void
    {
        // Convertir el objeto de dominio a entidad
        $userEntity = $this->mapDomainToEntity($user);

        $this->em->persist($userEntity);
        $this->em->flush();

        // Actualizar el ID en el objeto de dominio
        if ($user->id() === null) {
            $this->updateDomainObjectId($user, $userEntity->getId());
        }
    }

    public function findById(int $id): ?User
    {
        $userEntity = $this->em->getRepository(UserEntity::class)->find($id);

        if (!$userEntity) {
            return null;
        }

        return $this->mapEntityToDomain($userEntity);
    }

    public function findAll(): array
    {
        $userEntities = $this->em->getRepository(UserEntity::class)->findAll();

        return array_map([$this, 'mapEntityToDomain'], $userEntities);
    }

    public function remove(User $user): void
    {
        $userEntity = $this->em->getRepository(UserEntity::class)->find($user->id());

        if ($userEntity) {
            $this->em->remove($userEntity);
            $this->em->flush();
        }
    }

    public function findByEmail(string $email): ?User
    {
        $userEntity = $this->em->getRepository(UserEntity::class)
            ->findOneBy(['email' => $email]);

        if (!$userEntity) {
            return null;
        }

        return $this->mapEntityToDomain($userEntity);
    }


    /**
     * Método DELETE adicional para eliminar por ID directamente
     * (Útil si no tienes el objeto User pero sí el ID)
     */
    public function delete(int $id): bool
    {
        $userEntity = $this->em->getRepository(UserEntity::class)->find($id);

        if (!$userEntity) {
            return false;
        }

        $this->em->remove($userEntity);
        $this->em->flush();

        return true;
    }

    /**
     * Método DELETE que retorna el usuario eliminado
     */
    public function deleteAndReturn(int $id): ?User
    {
        $user = $this->findById($id);

        if (!$user) {
            return null;
        }

        $userEntity = $this->em->getRepository(UserEntity::class)->find($id);
        $this->em->remove($userEntity);
        $this->em->flush();

        return $user;
    }

    /**
     * Mapea entidad de Doctrine a objeto de dominio
     */
    private function mapEntityToDomain(UserEntity $userEntity): User
    {
        $roleEntity = $userEntity->getRole();
        $stateEntity = $userEntity->getState();

        $role = new Role($roleEntity->getId(), $roleEntity->getName());
        $state = new State($stateEntity->getId(), $stateEntity->getName());

        return new User(
            $userEntity->getId(),
            $userEntity->getName(),
            $userEntity->getEmail(),
            $userEntity->getDepartment(),
            $role,
            $state
        );
    }

    /**
     * Mapea objeto de dominio a entidad de Doctrine
     */
    private function mapDomainToEntity(User $user): UserEntity
    {
        // Buscar entidad existente o crear nueva
        if ($user->id() !== null) {
            $userEntity = $this->em->getRepository(UserEntity::class)->find($user->id());
            if (!$userEntity) {
                throw new \RuntimeException('UserEntity not found for ID: ' . $user->id());
            }
        } else {
            // Crear nueva entidad con valores por defecto
            $userEntity = new UserEntity();
        }

        // Obtener las entidades relacionadas (ESTAS NO DEBEN SER NULL)
        $roleEntity = $this->em->getRepository(RoleEntity::class)
            ->find($user->role()->id());
        $stateEntity = $this->em->getRepository(StateEntity::class)
            ->find($user->state()->id());

        if (!$roleEntity) {
            throw new \RuntimeException('RoleEntity not found for ID: ' . $user->role()->id());
        }
        if (!$stateEntity) {
            throw new \RuntimeException('StateEntity not found for ID: ' . $user->state()->id());
        }

        // Actualizar propiedades - estas NO deben ser null
        $userEntity->setName($user->name());
        $userEntity->setEmail($user->email());
        $userEntity->setDepartment($user->department());
        $userEntity->setRole($roleEntity); // ← Ya no será null
        $userEntity->setState($stateEntity); // ← Ya no será null

        return $userEntity;
    }

    /**
     * Actualiza el ID en el objeto de dominio usando reflection
     */
    private function updateDomainObjectId(User $user, int $id): void
    {
        $reflectionClass = new \ReflectionClass(User::class);

        try {
            $idProperty = $reflectionClass->getProperty('id');
            $idProperty->setAccessible(true);
            $idProperty->setValue($user, $id);
        } catch (\ReflectionException $e) {
            // Log the error or handle it appropriately
            throw new \RuntimeException('Failed to update domain object ID: ' . $e->getMessage());
        }
    }
}
