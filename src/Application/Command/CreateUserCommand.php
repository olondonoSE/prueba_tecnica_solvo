<?php

namespace App\Application\Command;

class CreateUserCommand
{
    public string $name;
    public string $email;
    public string $department;
    public int $roleId;
    public int $stateId;

    public function __construct(
        string $name,
        string $email,
        string $department,
        int $roleId,
        int $stateId
    ) {
        $this->name = $name;
        $this->email = $email;
        $this->department = $department;
        $this->roleId = $roleId;
        $this->stateId = $stateId;
    }
}
