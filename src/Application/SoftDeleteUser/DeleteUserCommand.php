<?php

declare(strict_types=1);

namespace App\Application\SoftDeleteUser;

final readonly class DeleteUserCommand
{
    public function __construct(
        public int $id,
    ) {}
}
