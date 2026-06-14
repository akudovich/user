<?php

declare(strict_types=1);

namespace App\Tests\Double;

use App\Application\Clock;
use DateTimeImmutable;

final readonly class FrozenClock implements Clock
{
    public function __construct(
        private DateTimeImmutable $dateTime,
    ) {}

    public function now(): DateTimeImmutable
    {
        return $this->dateTime;
    }
}
