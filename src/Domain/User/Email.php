<?php

declare(strict_types=1);

namespace App\Domain\User;

use App\Domain\Exception\InvalidEmail;

final readonly class Email
{
    public function __construct(
        private string $value
    ) {
        if (! filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw InvalidEmail::create($this->value);
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getDomain(): string
    {
        return substr($this->value, strpos($this->value, '@') + 1);
    }
}
