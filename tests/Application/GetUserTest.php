<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Application\Exception\UserNotFound;
use App\Application\GetUser\GetUserHandler;
use App\Application\GetUser\GetUserQuery;
use App\Domain\User;
use App\Tests\Double\InMemoryUserRepository;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class GetUserTest extends TestCase
{
    private InMemoryUserRepository $repository;

    protected function setUp(): void
    {
        $this->repository = new InMemoryUserRepository();
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

    public function testUserRead(): void
    {
        $handler = new GetUserHandler($this->repository);
        $view = $handler(new GetUserQuery(1));

        $this->assertEquals(1, $view->id);
        $this->assertEquals('john1234', $view->name);
        $this->assertEquals('john@example.com', $view->email);
        $this->assertEquals('notes', $view->notes);
    }

    public function testReadNotExisted(): void
    {
        $handler = new GetUserHandler($this->repository);
        $this->expectException(UserNotFound::class);

        $handler(new GetUserQuery(100));
    }

    public function testReadDeleted(): void
    {
        $user = $this->repository->get(1);
        self::assertNotNull($user);
        $user->softDelete(new DateTimeImmutable('2026-01-02 12:00:01'));
        $this->repository->save($user);
        $handler = new GetUserHandler($this->repository);
        $this->expectException(UserNotFound::class);

        $handler(new GetUserQuery(1));
    }
}
