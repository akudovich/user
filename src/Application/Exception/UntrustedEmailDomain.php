<?php

declare(strict_types=1);

namespace App\Application\Exception;

final class UntrustedEmailDomain extends ApplicationException
{
    public static function create(): self
    {
        return new self('Email domain is untrusted');
    }
}
