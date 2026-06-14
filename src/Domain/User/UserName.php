<?php

declare(strict_types=1);

namespace App\Domain\User;

use App\Domain\Exception\InvalidUserName;

final readonly class UserName
{
    public function __construct(
        private string $value,
    ) {
        if (strlen($value) < 8) {
            throw InvalidUserName::tooShort();
        }

        if (strlen($value) > 64) {
            throw InvalidUserName::tooLong();
        }

        if (! preg_match('/^[a-z0-9]+$/', $value)) {
            throw InvalidUserName::invalidCharacters();
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
