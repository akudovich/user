<?php

declare(strict_types=1);

namespace App\Application\Exception;

final class UserAlreadyExists extends ApplicationException
{
    public static function create(string $name, string $email): self
    {
        return new self(sprintf('User with name %s or email %s already exists', $name, $email));
    }
}
