<?php

namespace App\Domain\Repository;

use App\Domain\Model\State;

interface StateRepositoryInterface
{
    public function save(State $state): void;
    public function findById(int $id): ?State;
    public function findAll(): array;
    public function remove(State $state): void;
    public function findByName(string $name): ?State;
}
