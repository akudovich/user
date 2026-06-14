<?php

declare(strict_types=1);

namespace App\Domain;

use App\Domain\Exception\InvalidUserState;
use App\Domain\Exception\UserAlreadyDeleted;
use App\Domain\User\Email;
use App\Domain\User\UserName;
use DateTimeImmutable;

final class User
{
    public function __construct(
        private int|null $id,
        private UserName $name,
        private Email $email,
        private readonly DateTimeImmutable $created,
        private DateTimeImmutable|null $deleted = null,
        private string|null $notes = null,
    ) {
        $this->assertValid();
    }

    public static function create(UserName $name, Email $email, ?string $notes, DateTimeImmutable $created): self
    {
        return new self(null, $name, $email, $created, null, $notes);
    }

    public function getId(): int|null
    {
        return $this->id;
    }

    public function getName(): UserName
    {
        return $this->name;
    }

    public function rename(UserName $name): void
    {
        $this->assertNotDeleted();
        $this->name = $name;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function changeEmail(Email $email): void
    {
        $this->assertNotDeleted();
        $this->email = $email;
    }

    public function getCreated(): DateTimeImmutable
    {
        return $this->created;
    }

    public function isDeleted(): bool
    {
        return $this->deleted !== null;
    }

    public function getDeleted(): DateTimeImmutable|null
    {
        return $this->deleted;
    }

    public function softDelete(DateTimeImmutable $deleted): void
    {
        $this->assertNotDeleted();
        $this->deleted = $deleted;
        $this->assertValid();
    }

    public function getNotes(): string|null
    {
        return $this->notes;
    }

    public function changeNotes(string $notes): void
    {
        $this->assertNotDeleted();
        $this->notes = $notes;
    }

    private function assertNotDeleted(): void
    {
        if ($this->isDeleted()) {
            throw UserAlreadyDeleted::create($this->id);
        }
    }

    private function assertValid(): void
    {
        if ($this->deleted !== null && $this->deleted < $this->created) {
            throw InvalidUserState::deletedBeforeCreated($this->created, $this->deleted);
        }
    }
}
