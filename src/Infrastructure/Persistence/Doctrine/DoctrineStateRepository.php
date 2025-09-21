<?php

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\Repository\StateRepositoryInterface;
use App\Domain\Model\State;
use App\Infrastructure\Persistence\Doctrine\Entities\StateEntity;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineStateRepository implements StateRepositoryInterface
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function save(State $state): void
    {
        $stateEntity = $this->mapDomainToEntity($state);

        $this->entityManager->persist($stateEntity);
        $this->entityManager->flush();

        // Actualizar el ID en el objeto de dominio si es nuevo
        if ($state->id() === null) {
            $this->updateDomainObjectId($state, $stateEntity->getId());
        }
    }

    public function findById(int $id): ?State
    {
        $stateEntity = $this->entityManager->getRepository(StateEntity::class)->find($id);

        if (!$stateEntity) {
            return null;
        }

        return $this->mapEntityToDomain($stateEntity);
    }

    public function findAll(): array
    {
        $stateEntities = $this->entityManager->getRepository(StateEntity::class)->findAll();

        return array_map([$this, 'mapEntityToDomain'], $stateEntities);
    }

    public function remove(State $state): void
    {
        $stateEntity = $this->entityManager->getRepository(StateEntity::class)->find($state->id());

        if ($stateEntity) {
            $this->entityManager->remove($stateEntity);
            $this->entityManager->flush();
        }
    }

    public function findByName(string $name): ?State
    {
        $stateEntity = $this->entityManager->getRepository(StateEntity::class)
            ->findOneBy(['name' => $name]);

        if (!$stateEntity) {
            return null;
        }

        return $this->mapEntityToDomain($stateEntity);
    }

    /**
     * Mapea entidad de Doctrine a objeto de dominio State
     */
    private function mapEntityToDomain(StateEntity $stateEntity): State
    {
        return new State(
            $stateEntity->getId(),
            $stateEntity->getName()
        );
    }

    /**
     * Mapea objeto de dominio State a entidad de Doctrine
     */
    private function mapDomainToEntity(State $state): StateEntity
    {
        if ($state->id() !== null) {
            $stateEntity = $this->entityManager->getRepository(StateEntity::class)->find($state->id());
            if (!$stateEntity) {
                throw new \RuntimeException('StateEntity not found for ID: ' . $state->id());
            }
        } else {
            $stateEntity = new StateEntity();
        }

        $stateEntity->setName($state->name());

        return $stateEntity;
    }

    /**
     * Actualiza el ID en el objeto de dominio usando reflection
     */
    private function updateDomainObjectId(State $state, int $id): void
    {
        $reflectionClass = new \ReflectionClass(State::class);

        try {
            $idProperty = $reflectionClass->getProperty('id');
            $idProperty->setAccessible(true);
            $idProperty->setValue($state, $id);
        } catch (\ReflectionException $e) {
            throw new \RuntimeException('Failed to update domain object ID: ' . $e->getMessage());
        }
    }
}
