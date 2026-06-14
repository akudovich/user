<?php

declare(strict_types=1);

namespace App\Application\Exception;

final class UserNameAlreadyTaken extends ApplicationException
{
    public static function create(): self
    {
        return new self('User name is already taken');
    }
}
