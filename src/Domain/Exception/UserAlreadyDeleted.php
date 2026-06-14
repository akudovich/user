<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class UserAlreadyDeleted extends DomainException
{
    public static function create(int|null $id): self
    {
        return new self(sprintf('User with id %d already deleted', $id));
    }
}
