<?php

namespace App\Application\Command;

class UpdateUserCommand
{
    public int $id;
    public string $name;
    public string $email;
    public string $department;
    public int $roleId;
    public int $stateId;

    public function __construct(
        int $id,
        string $name,
        string $email,
        string $department,
        int $roleId,
        int $stateId
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->department = $department;
        $this->roleId = $roleId;
        $this->stateId = $stateId;
    }
}
