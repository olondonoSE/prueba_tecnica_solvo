<?php

namespace App\Infrastructure\Persistence\Doctrine\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "users")]
class UserEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255)]
    private string $name = '';

    #[ORM\Column(type: "string", length: 255, unique: true)]
    private string $email = '';

    #[ORM\Column(type: "string", length: 255)]
    private string $department = '';

    #[ORM\ManyToOne(targetEntity: RoleEntity::class)]
    #[ORM\JoinColumn(nullable: true)] // ← Cambiar a true temporalmente
    private ?RoleEntity $role = null; // ← Hacer nullable

    #[ORM\ManyToOne(targetEntity: StateEntity::class)]
    #[ORM\JoinColumn(nullable: true)] // ← Cambiar a true temporalmente
    private ?StateEntity $state = null; // ← Hacer nullable

    public function __construct(
        string $name = '',
        string $email = '',
        string $department = '',
        ?RoleEntity $role = null,
        ?StateEntity $state = null
    ) {
        $this->name = $name;
        $this->email = $email;
        $this->department = $department;
        $this->role = $role;
        $this->state = $state;
    }

    // SETTERS (Aceptan null)
    public function setRole(?RoleEntity $role): void
    {
        $this->role = $role;
    }

    public function setState(?StateEntity $state): void
    {
        $this->state = $state;
    }

    // ... otros getters y setters ...
    public function setName(string $name): void { $this->name = $name; }
    public function setEmail(string $email): void { $this->email = $email; }
    public function setDepartment(string $department): void { $this->department = $department; }

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getEmail(): string { return $this->email; }
    public function getDepartment(): string { return $this->department; }
    public function getRole(): ?RoleEntity { return $this->role; }
    public function getState(): ?StateEntity { return $this->state; }
}
