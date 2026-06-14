<?php

declare(strict_types=1);

namespace App\Application;

interface Clock
{
    public function now(): \DateTimeImmutable;
}
