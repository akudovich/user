<?php

declare(strict_types=1);

namespace App\Application\Exception;

final class EmailAlreadyTaken extends ApplicationException
{
    public static function create(): self
    {
        return new self('Email is already taken');
    }
}
