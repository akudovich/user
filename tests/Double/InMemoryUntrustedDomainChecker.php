<?php

declare(strict_types=1);

namespace App\Tests\Double;

use App\Application\UntrustedDomainChecker;

final readonly class InMemoryUntrustedDomainChecker implements UntrustedDomainChecker
{
    /**
     * @param string[] $untrustedDomains
     */
    public function __construct(
        private array $untrustedDomains,
    ) {}

    public function isDomainUntrusted(string $domain): bool
    {
        if (in_array($domain, $this->untrustedDomains, true)) {
            return true;
        }

        // if the domain is a subdomain, check the parent domain
        if (str_contains($domain, '.')) {
            return $this->isDomainUntrusted(substr($domain, strpos($domain, '.') + 1));
        }

        return false;
    }
}
