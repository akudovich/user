<?php

declare(strict_types=1);

namespace App\Application\Exception;

final class UserNotFound extends ApplicationException
{
    public static function create(int $id): self
    {
        return new self(sprintf('User with id %d not found', $id));
    }
}
