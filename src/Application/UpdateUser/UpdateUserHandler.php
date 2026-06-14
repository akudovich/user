<?php

declare(strict_types=1);

namespace App\Application\UpdateUser;

use App\Application\AuditLogger;
use App\Application\Exception\EmailAlreadyTaken;
use App\Application\Exception\ForbiddenUserName;
use App\Application\Exception\UntrustedEmailDomain;
use App\Application\Exception\UserNameAlreadyTaken;
use App\Application\Exception\UserNotFound;
use App\Application\ForbiddenWordsChecker;
use App\Application\TransactionManager;
use App\Application\UntrustedDomainChecker;
use App\Application\UserRepository;
use App\Domain\User;
use App\Domain\User\Email;
use App\Domain\User\UserName;

final readonly class UpdateUserHandler
{
    public function __construct(
        private UserRepository $repository,
        private ForbiddenWordsChecker $forbiddenWordsChecker,
        private UntrustedDomainChecker $untrustedDomainChecker,
        private TransactionManager $transactionManager,
        private AuditLogger $auditLogger,
    ) {}

    public function __invoke(UpdateUserCommand $command): void
    {
        $user = $this->repository->get($command->id);
        if ($user === null || $user->isDeleted()) {
            throw UserNotFound::create($command->id);
        }

        $before = clone $user;

        $this->changeUserNameIfNeeded($command, $user);
        $this->changeEmailIfNeeded($command, $user);
        $this->changeNotesIfNeeded($command, $user);

        $this->transactionManager->transactional(function () use ($before, $user): void {
            $this->repository->save($user);
            $this->auditLogger->logUserChanged($before, $user);
        });
    }

    private function changeUserNameIfNeeded(UpdateUserCommand $command, User $user): void
    {
        if ($command->name === null || $command->name === $user->getName()->getValue()) {
            return;
        }
        $name = new UserName($command->name);
        if ($this->repository->existsByName($name)) {
            throw UserNameAlreadyTaken::create();
        }
        if ($this->forbiddenWordsChecker->isUserNameForbidden($name)) {
            throw ForbiddenUserName::create();
        }
        $user->rename($name);
    }

    private function changeEmailIfNeeded(UpdateUserCommand $command, User $user): void
    {
        if ($command->email === null || $command->email === $user->getEmail()->getValue()) {
            return;
        }

        $email = new Email($command->email);
        if ($this->repository->existsByEmail($email)) {
            throw EmailAlreadyTaken::create();
        }
        if ($this->untrustedDomainChecker->isDomainUntrusted($email->getDomain())) {
            throw UntrustedEmailDomain::create();
        }
        $user->changeEmail($email);
    }

    private function changeNotesIfNeeded(UpdateUserCommand $command, User $user): void
    {
        if ($command->notes === null || $command->notes === $user->getNotes()) {
            return;
        }
        $user->changeNotes($command->notes);
    }
}
