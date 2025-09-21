<?php

namespace App\Application\Command;

class DeleteUserCommand
{
    public int $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }
}
