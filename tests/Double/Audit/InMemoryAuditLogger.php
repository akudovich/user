<?php

declare(strict_types=1);

namespace App\Tests\Double\Audit;

use App\Application\AuditLogger;
use App\Application\Clock;
use App\Domain\User;
use LogicException;

final class InMemoryAuditLogger implements AuditLogger
{
    /**
     * @var list<AuditRecord>
     */
    private array $records = [];

    public function __construct(
        private readonly Clock $clock,
    ) {}

    public function logUserChanged(User $before, User $after): void
    {
        $changes = [];
        if ($before->getName() !== $after->getName()) {
            $changes['name'] = [
                'old' => $before->getName()->getValue(),
                'new' => $after->getName()->getValue(),
            ];
        }
        if ($before->getEmail() !== $after->getEmail()) {
            $changes['email'] = [
                'old' => $before->getEmail()->getValue(),
                'new' => $after->getEmail()->getValue(),
            ];
        }
        if ($before->getNotes() !== $after->getNotes()) {
            $changes['notes'] = [
                'old' => $before->getNotes(),
                'new' => $after->getNotes(),
            ];
        }
        if ($before->getDeleted() !== $after->getDeleted()) {
            $changes['deleted'] = [
                'old' => $before->getDeleted()?->format('Y-m-d H:i:s'),
                'new' => $after->getDeleted()?->format('Y-m-d H:i:s'),
            ];
        }
        if ($after->getId() === null) {
            throw new LogicException('User ID should be set after autoincrement.');
        }
        $this->records[] = new AuditRecord($after->getId(), $changes, $this->clock->now());
    }

    /**
     * @return list<AuditRecord>
     */
    public function getRecords(): array
    {
        return $this->records;
    }
}
