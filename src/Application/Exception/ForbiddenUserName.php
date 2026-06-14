<?php

declare(strict_types=1);

namespace App\Application\Exception;

final class ForbiddenUserName extends ApplicationException
{
    public static function create(): self
    {
        return new self('User name is forbidden');
    }
}
