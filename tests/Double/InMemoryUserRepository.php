<?php

declare(strict_types=1);

namespace App\Tests\Double;

use App\Application\Exception\UserAlreadyExists;
use App\Application\UserRepository;
use App\Domain\User;
use App\Domain\User\Email;
use App\Domain\User\UserName;

final class InMemoryUserRepository implements UserRepository
{
    public function __construct(
        /**
         * @var array<int, User>
         */
        private array $users = []
    ) {}

    public function save(User $user): int
    {
        if ($this->isNew($user)) {
            if ($this->existsByEmail($user->getEmail()) or $this->existsByName($user->getName())) {
                throw UserAlreadyExists::create($user->getName()->getValue(), $user->getEmail()->getValue());
            }
            $this->autoincrement($user);
        }
        if ($user->getId() === null) {
            throw new \LogicException('User ID should be set after autoincrement.');
        }
        $this->users[$user->getId()] = $user;
        return $user->getId();
    }

    public function get(int $id): User|null
    {
        $user = $this->users[$id] ?? null;
        if ($user === null || $user->isDeleted()) {
            return null;
        }
        return clone $user;
    }

    public function existsByEmail(Email $email): bool
    {
        return array_reduce($this->users, function (bool $exists, User $user) use ($email) {
            return $exists || $user->getEmail()->getValue() === $email->getValue();
        }, false);
    }

    public function existsByName(UserName $name): bool
    {
        return array_reduce($this->users, function (bool $exists, User $user) use ($name) {
            return $exists || $user->getName()->getValue() === $name->getValue();
        }, false);
    }

    private function autoincrement(User $user): void
    {
        if ($this->isNew($user)) {
            // set user id to the next available integer through Reflection
            $ref = new \ReflectionProperty(User::class, 'id');
            $ref->setValue($user, (array_key_last($this->users) ?? 0) + 1);
        }
    }

    private function isNew(User $user): bool
    {
        return $user->getId() === null;
    }
}
