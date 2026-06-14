<?php

declare(strict_types=1);

namespace App\Application\GetUser;

use App\Application\Exception\UserNotFound;
use App\Application\UserRepository;
use LogicException;

final readonly class GetUserHandler
{
    public function __construct(
        private UserRepository $repository,
    ) {}

    public function __invoke(GetUserQuery $query): UserView
    {
        $user = $this->repository->get($query->id);

        if ($user === null || $user->isDeleted()) {
            throw UserNotFound::create($query->id);
        }

        if ($user->getId() === null) {
            throw new LogicException('Persisted user must have an id.');
        }

        return new UserView(
            $user->getId(),
            $user->getName()->getValue(),
            $user->getEmail()->getValue(),
            $user->getNotes(),
        );
    }
}
