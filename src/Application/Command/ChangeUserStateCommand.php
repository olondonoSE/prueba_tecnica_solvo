<?php
namespace App\Application\Command;

class ChangeUserStateCommand
{
    public function __construct(
        public readonly int $userId,
        public readonly int $newStateId
    ) {}
}
