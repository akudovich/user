<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class InvalidEmail extends DomainException
{
    public static function create(string $email): self
    {
        return new self(sprintf('Invalid email format: %s', $email));
    }
}
