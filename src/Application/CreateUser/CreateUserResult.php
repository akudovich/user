<?php

declare(strict_types=1);

namespace App\Application\CreateUser;

final readonly class CreateUserResult
{
    public function __construct(
        public int $id,
    ) {}
}
