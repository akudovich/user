<?php

declare(strict_types=1);

namespace App\Tests\Domain;

use App\Domain\Exception\InvalidUserState;
use App\Domain\Exception\UserAlreadyDeleted;
use App\Domain\User;
use App\Domain\User\Email;
use App\Domain\User\UserName;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function testCreation(): void
    {
        $user = $this->createUser($time = new DateTimeImmutable());
        $this->assertNull($user->getId());
        $this->assertEquals('johndoe8', $user->getName()->getValue());
        $this->assertEquals('johndoe@example.com', $user->getEmail()->getValue());
        $this->assertInstanceOf(DateTimeImmutable::class, $user->getCreated());
        $this->assertEquals($time, $user->getCreated());
        $this->assertNull($user->getDeleted());
        $this->assertNull($user->getNotes());
    }

    public function testUserDeletion(): void
    {
        $user = $this->createUser();
        $user->softDelete($deletedTime = new DateTimeImmutable());
        $this->assertInstanceOf(DateTimeImmutable::class, $user->getDeleted());
        $this->assertEquals($deletedTime, $user->getDeleted());
    }

    public function testCreateDeleted(): void
    {
        $user = new User(
            id: null,
            name: new UserName('johndoe8'),
            email: new Email('johndoe@example.com'),
            created: $created = new DateTimeImmutable(),
            deleted: $created,
        );
        $this->assertInstanceOf(User::class, $user);
    }

    public function testCreateInvalidDeleteTime(): void
    {
        $this->expectException(InvalidUserState::class);
        new User(
            id: null,
            name: new UserName('johndoe8'),
            email: new Email('johndoe@example.com'),
            created: new DateTimeImmutable(),
            deleted: new DateTimeImmutable('-1 day'),
        );
    }

    public function testDeleteBeforeCreation(): void
    {
        $user = $this->createUser();
        $this->expectException(InvalidUserState::class);
        $user->softDelete(new DateTimeImmutable('-1 day'));
    }

    public function testDeleteAfterDeletion(): void
    {
        $user = $this->createUser();
        $user->softDelete(new DateTimeImmutable());
        $this->expectException(UserAlreadyDeleted::class);
        $user->softDelete(new DateTimeImmutable());
    }

    public function testRenameAfterDeletion(): void
    {
        $user = $this->createUser();
        $user->softDelete(new DateTimeImmutable());
        $this->expectException(UserAlreadyDeleted::class);
        $user->rename(new UserName('updatedname'));
    }

    public function testChangeEmailAfterDeletion(): void
    {
        $user = $this->createUser();
        $user->softDelete(new DateTimeImmutable());
        $this->expectException(UserAlreadyDeleted::class);
        $user->changeEmail(new Email('updatedemail@test.com'));
    }

    public function testChangeNotesAfterDeletion(): void
    {
        $user = $this->createUser();
        $user->softDelete(new DateTimeImmutable());
        $this->expectException(UserAlreadyDeleted::class);
        $user->changeNotes('This is a note');
    }

    public function testRename(): void
    {
        $user = $this->createUser();
        $user->rename(new UserName('janejohnson'));
        $this->assertEquals('janejohnson', $user->getName()->getValue());
    }

    public function testChangeEmail(): void
    {
        $user = $this->createUser();
        $user->changeEmail(new Email('janejohnson@example.com'));
        $this->assertEquals('janejohnson@example.com', $user->getEmail()->getValue());
    }

    public function testChangeNotes(): void
    {
        $user = $this->createUser();
        $user->changeNotes('This is a note');
        $this->assertEquals('This is a note', $user->getNotes());
    }

    public function createUser(DateTimeImmutable $time = new DateTimeImmutable()): User
    {
        return User::create(
            name: new UserName('johndoe8'),
            email: new Email('johndoe@example.com'),
            notes: null,
            created: $time
        );
    }
}
