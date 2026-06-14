<?php

declare(strict_types=1);

namespace App\Application\UpdateUser;

final readonly class UpdateUserCommand
{
    public function __construct(
        public int $id,
        public string|null $name = null,
        public string|null $email = null,
        public string|null $notes = null,
    ) {}
}
