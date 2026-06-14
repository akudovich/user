<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class InvalidUserName extends DomainException
{
    public static function tooShort(): self
    {
        return new self('User name is too short');
    }

    public static function invalidCharacters(): self
    {
        return new self('User name contains invalid characters');
    }

    public static function tooLong(): self
    {
        return new self('User name is too long');
    }
}
