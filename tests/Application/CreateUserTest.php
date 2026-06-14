<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Application\CreateUser\CreateUserCommand;
use App\Application\CreateUser\CreateUserHandler;
use App\Application\Exception\EmailAlreadyTaken;
use App\Application\Exception\ForbiddenUserName;
use App\Application\Exception\UntrustedEmailDomain;
use App\Application\Exception\UserNameAlreadyTaken;
use App\Domain\User;
use App\Tests\Double\FrozenClock;
use App\Tests\Double\InMemoryForbiddenWordsChecker;
use App\Tests\Double\InMemoryUntrustedDomainChecker;
use App\Tests\Double\InMemoryUserRepository;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class CreateUserTest extends TestCase
{
    private InMemoryUserRepository $repository;

    protected function setUp(): void
    {
        $this->repository = new InMemoryUserRepository();
    }

    public function testCreatesUser(): void
    {
        $handler = $this->getUserHandler();

        $user = $handler(new CreateUserCommand(name: 'john1234', email: 'john@example.com', notes: 'test notes'));

        self::assertSame(1, $user->id);
        self::assertNotNull($this->repository->get($user->id));
    }

    public function testForbiddenName(): void
    {
        $this->expectException(ForbiddenUserName::class);

        $handler = $this->getUserHandler();
        $handler(new CreateUserCommand(name: 'adminadmin', email: 'john@example.com', notes: 'test notes'));
    }

    public function testUntrustedDomain(): void
    {
        $this->expectException(UntrustedEmailDomain::class);

        $handler = $this->getUserHandler();
        $handler(new CreateUserCommand(name: 'john1234', email: 'john@spam.test', notes: 'test notes'));
    }

    public function testUntrustedSubDomain(): void
    {
        $this->expectException(UntrustedEmailDomain::class);

        $handler = $this->getUserHandler();
        $handler(new CreateUserCommand(name: 'john1234', email: 'john@more.spam.test', notes: 'test notes'));
    }

    public function testNameAlreadyExists(): void
    {
        $this->repository->save(
            User::create(new User\UserName('john1234'), new User\Email(
                'john@example.com'
            ), null, new DateTimeImmutable())
        );
        $this->expectException(UserNameAlreadyTaken::class);

        $handler = $this->getUserHandler();
        $handler(new CreateUserCommand(name: 'john1234', email: 'john1234@domain.com', notes: 'test notes'));
    }

    public function testEmailAlreadyExists(): void
    {
        $this->repository->save(
            User::create(new User\UserName('john1234'), new User\Email(
                'john@example.com'
            ), null, new DateTimeImmutable())
        );
        $this->expectException(EmailAlreadyTaken::class);

        $handler = $this->getUserHandler();
        $handler(new CreateUserCommand(name: 'mike1234', email: 'john@example.com', notes: 'test notes'));
    }

    public function getUserHandler(): CreateUserHandler
    {
        return new CreateUserHandler(
            userRepository: $this->repository,
            clock: new FrozenClock(new DateTimeImmutable('2026-01-01 12:00:00')),
            forbiddenWordsChecker: new InMemoryForbiddenWordsChecker(['admin']),
            untrustedDomainChecker: new InMemoryUntrustedDomainChecker(['spam.test']),
        );
    }
}
