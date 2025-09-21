<?php
namespace App\Domain\Event;

use App\Domain\Model\User;

class UserStateChanged
{
    private User $user;
    private int $oldStateId;
    public function __construct(User $user, int $oldStateId)
    {
        $this->user = $user;
        $this->oldStateId = $oldStateId;
    }
    public function user(): User { return $this->user; }
    public function oldStateId(): int { return $this->oldStateId; }
}
