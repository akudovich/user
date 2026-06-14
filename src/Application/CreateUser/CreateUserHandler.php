<?php

declare(strict_types=1);

namespace App\Application\CreateUser;

use App\Application\Clock;
use App\Application\Exception\EmailAlreadyTaken;
use App\Application\Exception\ForbiddenUserName;
use App\Application\Exception\UntrustedEmailDomain;
use App\Application\Exception\UserNameAlreadyTaken;
use App\Application\ForbiddenWordsChecker;
use App\Application\UntrustedDomainChecker;
use App\Application\UserRepository;
use App\Domain\User;
use App\Domain\User\Email;
use App\Domain\User\UserName;

final readonly class CreateUserHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private Clock $clock,
        private ForbiddenWordsChecker $forbiddenWordsChecker,
        private UntrustedDomainChecker $untrustedDomainChecker,
    ) {}

    public function __invoke(CreateUserCommand $command): CreateUserResult
    {
        $name = new UserName($command->name);
        $email = new Email($command->email);

        if ($this->forbiddenWordsChecker->isUserNameForbidden($name)) {
            throw ForbiddenUserName::create();
        }

        if ($this->untrustedDomainChecker->isDomainUntrusted($email->getDomain())) {
            throw UntrustedEmailDomain::create();
        }

        if ($this->userRepository->existsByName($name)) {
            throw UserNameAlreadyTaken::create();
        }

        if ($this->userRepository->existsByEmail($email)) {
            throw EmailAlreadyTaken::create();
        }

        $user = User::create(name: $name, email: $email, notes: $command->notes, created: $this->clock->now());

        $id = $this->userRepository->save($user);

        return new CreateUserResult($id);
    }
}
