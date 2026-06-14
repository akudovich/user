<?php

declare(strict_types=1);

namespace App\Application;

interface UntrustedDomainChecker
{
    public function isDomainUntrusted(string $domain): bool;
}
