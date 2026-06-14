<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Application\Exception\UserNotFound;
use App\Application\SoftDeleteUser\DeleteUserCommand;
use App\Application\SoftDeleteUser\DeleteUserHandler;
use App\Domain\User;
use App\Tests\Double\Audit\InMemoryAuditLogger;
use App\Tests\Double\FrozenClock;
use App\Tests\Double\InMemoryTransactionManager;
use App\Tests\Double\InMemoryUserRepository;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class SoftDeleteUserTest extends TestCase
{
    private InMemoryUserRepository $repository;

    private InMemoryAuditLogger $auditLogger;

    protected function setUp(): void
    {
        $this->repository = new InMemoryUserRepository();
        $this->auditLogger = new InMemoryAuditLogger(new FrozenClock(new DateTimeImmutable('2026-01-01 12:00:00')));
    }

    public function testUserDelete(): void
    {
        $this->repository->save(
            User::create(new User\UserName('john1234'), new User\Email('test@domain.com'), null, new DateTimeImmutable(
                '2026-01-01 12:00:00'
            ))
        );
        $handler = $this->getUserHandler();
        $handler(new DeleteUserCommand(1));

        self::assertNull($this->repository->get(1));
        self::assertCount(1, $this->auditLogger->getRecords());
        $auditRecord = $this->auditLogger->getRecords()[0];
        self::assertEquals(1, $auditRecord->id);
        self::assertEquals([
            'deleted' => [
                'old' => null,
                'new' => '2026-01-01 12:00:00',
            ],
        ], $auditRecord->changes);
    }

    public function testDeleteNonExistingUser(): void
    {
        $this->expectException(UserNotFound::class);

        $handler = $this->getUserHandler();
        $handler(new DeleteUserCommand(1));
    }

    public function testDoubleDeleteTreatsUserAsNotFound(): void
    {

        $this->repository->save(
            User::create(
                new User\UserName('john1234'),
                new User\Email('test@domain.com'),
                null,
                new DateTimeImmutable('2026-01-01 12:00:00'),
            )
        );

        $handler = $this->getUserHandler();

        $handler(new DeleteUserCommand(1));

        $this->expectException(UserNotFound::class);

        $handler(new DeleteUserCommand(1));

    }

    public function getUserHandler(): DeleteUserHandler
    {
        return new DeleteUserHandler(
            clock: new FrozenClock(new DateTimeImmutable('2026-01-01 12:00:00')),
            repository: $this->repository,
            transactionManager: new InMemoryTransactionManager(),
            auditLogger: $this->auditLogger,
        );
    }
}
