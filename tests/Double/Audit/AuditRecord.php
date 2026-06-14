<?php

declare(strict_types=1);

namespace App\Tests\Double\Audit;

final readonly class AuditRecord
{
    public function __construct(
        public int $id,
        /**
         * @var array<string, array{old: mixed, new: mixed}>
         */
        public array $changes,
        public \DateTimeImmutable $created,
    ) {}
}
