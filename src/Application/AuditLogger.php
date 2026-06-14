<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\User;

interface AuditLogger
{
    public function logUserChanged(User $before, User $after): void;
}
