<?php

declare(strict_types=1);

namespace App\Application\GetUser;

final readonly class UserView
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public string|null $notes = null,
    ) {}
}
