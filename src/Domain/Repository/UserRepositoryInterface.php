<?php
namespace App\Domain\Repository;

use App\Domain\Model\User;

interface UserRepositoryInterface
{
    public function save(User $user): void;
    public function findById(int $id): ?User;
    public function findAll(): array;
    public function remove(User $user): void;
    public function findByEmail(string $email): ?User;
}
