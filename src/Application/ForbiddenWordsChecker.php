<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\User\UserName;

interface ForbiddenWordsChecker
{
    public function isUserNameForbidden(UserName $name): bool;
}
