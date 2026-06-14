<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Application\Exception\EmailAlreadyTaken;
use App\Application\Exception\ForbiddenUserName;
use App\Application\Exception\UntrustedEmailDomain;
use App\Application\Exception\UserNameAlreadyTaken;
use App\Application\Exception\UserNotFound;
use App\Application\UpdateUser\UpdateUserCommand;
use App\Application\UpdateUser\UpdateUserHandler;
use App\Domain\User;
use App\Tests\Double\Audit\InMemoryAuditLogger;
use App\Tests\Double\FrozenClock;
use App\Tests\Double\InMemoryForbiddenWordsChecker;
use App\Tests\Double\InMemoryTransactionManager;
use App\Tests\Double\InMemoryUntrustedDomainChecker;
use App\Tests\Double\InMemoryUserRepository;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class UpdateUserTest extends TestCase
{
    private InMemoryUserRepository $repository;

    private InMemoryAuditLogger $auditLogger;

    protected function setUp(): void
    {
        $this->repository = new InMemoryUserRepository();
        $this->auditLogger = new InMemoryAuditLogger(new FrozenClock(new DateTimeImmutable('2026-01-01 12:00:00')));
        // create a user to update
        $this->repository->save(
            User::create(
                name: new User\UserName('john1234'),
                email: new User\Email('john@example.com'),
                notes: 'notes',
                created: new DateTimeImmutable('2026-01-01 12:00:00'),
            )
        );
    }

    /**
     * @return array<string, UpdateUserCommand[]>
     */
    public static function commandProvider(): array
    {
        return [
            'test full update user' => [
                new UpdateUserCommand(
                    1,
                    name: 'updatedjohn1234',
                    email: 'updatejohn@example.com',
                    notes: 'updated notes'
                ),
            ],
            'test no update empty motes' => [
                new UpdateUserCommand(1, name: 'updatedjohn1234', email: 'updatejohn@example.com', notes: null),
            ],
            'test no update empty email' => [
                new UpdateUserCommand(1, name: 'updatedjohn1234', email: null, notes: 'updated notes'),
            ],
            'test no update empty name' => [
                new UpdateUserCommand(1, name: null, email: 'updatejohn@example.com', notes: 'updated notes'),
            ],
            'test update only name' => [new UpdateUserCommand(1, name: 'updatedjohn1234')],
            'test update only email' => [new UpdateUserCommand(1, email: 'updatejohn@example.com')],
            'test update only notes' => [new UpdateUserCommand(1, notes: 'updated notes')],
        ];
    }

    #[DataProvider('commandProvider')]
    public function testUpdateUser(UpdateUserCommand $command): void
    {
        $handler = $this->getUserHandler();
        $handler($command);
        $updatedUser = $this->repository->get(1);

        self::assertNotNull($updatedUser);
        $neededAuditRecordChanges = [];
        if ($command->name !== null) {
            self::assertEquals($command->name, $updatedUser->getName()->getValue());
            $neededAuditRecordChanges['name'] = [
                'old' => 'john1234',
                'new' => $command->name,
            ];
        }
        if ($command->email !== null) {
            self::assertEquals($command->email, $updatedUser->getEmail()->getValue());
            $neededAuditRecordChanges['email'] = [
                'old' => 'john@example.com',
                'new' => $command->email,
            ];
        }
        if ($command->notes !== null) {
            self::assertEquals($command->notes, $updatedUser->getNotes());
            $neededAuditRecordChanges['notes'] = [
                'old' => 'notes',
                'new' => $command->notes,
            ];
        }
        self::assertEquals('2026-01-01 12:00:00', $updatedUser->getCreated()->format('Y-m-d H:i:s'));
        self::assertNull($updatedUser->getDeleted());
        self::assertCount(1, $this->auditLogger->getRecords());
        $auditRecord = $this->auditLogger->getRecords()[0];
        self::assertEquals(1, $auditRecord->id);
        self::assertEquals($neededAuditRecordChanges, $auditRecord->changes);
    }

    public function testForbiddenName(): void
    {
        $this->expectException(ForbiddenUserName::class);

        $handler = $this->getUserHandler();
        $handler(new UpdateUserCommand(id: 1, name: 'adminadmin'));
    }

    public function testUntrustedDomain(): void
    {
        $this->expectException(UntrustedEmailDomain::class);

        $handler = $this->getUserHandler();
        $handler(new UpdateUserCommand(id: 1, email: 'john@spam.test'));
    }

    public function testUntrustedSubDomain(): void
    {
        $this->expectException(UntrustedEmailDomain::class);

        $handler = $this->getUserHandler();
        $handler(new UpdateUserCommand(id: 1, email: 'john@more.spam.test'));
    }

    public function testNameAlreadyExists(): void
    {
        $this->repository->save(
            User::create(
                name: new User\UserName('updatedjohn1234'),
                email: new User\Email('updatedjohn@example.com'),
                notes: 'notes',
                created: new DateTimeImmutable('2026-01-01 12:00:00'),
            )
        );
        $this->expectException(UserNameAlreadyTaken::class);

        $handler = $this->getUserHandler();
        $handler(new UpdateUserCommand(1, name: 'updatedjohn1234'));
    }

    public function testEmailAlreadyExists(): void
    {
        $this->repository->save(
            User::create(
                name: new User\UserName('updatedjohn1234'),
                email: new User\Email('updatedjohn@example.com'),
                notes: 'notes',
                created: new DateTimeImmutable('2026-01-01 12:00:00'),
            )
        );
        $this->expectException(EmailAlreadyTaken::class);

        $handler = $this->getUserHandler();
        $handler(new UpdateUserCommand(1, email: 'updatedjohn@example.com'));
    }

    public function testUpdateNotExistentUser(): void
    {
        $this->expectException(UserNotFound::class);
        $handler = $this->getUserHandler();
        $handler(new UpdateUserCommand(100, email: 'updatedjohn@example.com'));
    }

    public function getUserHandler(): UpdateUserHandler
    {
        return new UpdateUserHandler(
            repository: $this->repository,
            forbiddenWordsChecker: new InMemoryForbiddenWordsChecker(['admin']),
            untrustedDomainChecker: new InMemoryUntrustedDomainChecker(['spam.test']),
            transactionManager: new InMemoryTransactionManager(),
            auditLogger: $this->auditLogger,
        );
    }
}
