<?php

namespace App\Domain\Exception;

class NotFoundException extends \RuntimeException
{
    public static function forEntity(string $entity, int|string $id): self
    {
        return new self(sprintf('%s con ID %s no fue encontrado.', $entity, $id));
    }
}
