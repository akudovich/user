<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use DateTimeImmutable;

final class InvalidUserState extends DomainException
{
    public static function deletedBeforeCreated(DateTimeImmutable $created, DateTimeImmutable $deleted): self
    {
        return new self(sprintf('User deleted before created: %s < %s', $deleted->format('c'), $created->format('c')));
    }
}
