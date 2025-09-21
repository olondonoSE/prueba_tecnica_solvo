<?php
namespace App\Domain\Model;

use App\Domain\Model\Role;
use App\Domain\Model\State;

class User
{
    private ?int $id;
    private string $name;
    private string $email;
    private string $department;
    private Role $role;
    private State $state;

    public function __construct(?int $id, string $name, string $email, string $department, Role $role, State $state)
    {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->department = $department;
        $this->role = $role;
        $this->state = $state;
    }

    public function id(): ?int { return $this->id; }
    public function name(): string { return $this->name; }
    public function email(): string { return $this->email; }
    public function department(): string { return $this->department; }
    public function role(): Role { return $this->role; }
    public function state(): State { return $this->state; }

    public function changeState(State $newState): void
    {
        $this->state = $newState;
    }

    // más métodos de negocio si hace falta...

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function setDepartment(string $department): void
    {
        $this->department = $department;
    }

    public function setRole(Role $role): void
    {
        $this->role = $role;
    }

    public function setState(State $state): void
    {
        $this->state = $state;
    }
}
