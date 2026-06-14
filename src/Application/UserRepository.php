<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\User;
use App\Domain\User\Email;
use App\Domain\User\UserName;

interface UserRepository
{
    public function save(User $user): int;

    public function get(int $id): User|null;

    public function existsByEmail(Email $email): bool;

    public function existsByName(UserName $name): bool;
}
