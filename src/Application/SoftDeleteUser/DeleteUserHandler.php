<?php

declare(strict_types=1);

namespace App\Application\SoftDeleteUser;

use App\Application\AuditLogger;
use App\Application\Clock;
use App\Application\Exception\UserNotFound;
use App\Application\TransactionManager;
use App\Application\UserRepository;

final readonly class DeleteUserHandler
{
    public function __construct(
        private Clock $clock,
        private UserRepository $repository,
        private TransactionManager $transactionManager,
        private AuditLogger $auditLogger,
    ) {}

    public function __invoke(DeleteUserCommand $command): void
    {
        $user = $this->repository->get($command->id);
        if ($user === null) {
            throw UserNotFound::create($command->id);
        }
        $before = clone $user;
        $user->softDelete($this->clock->now());

        $this->transactionManager->transactional(function () use ($before, $user): void {
            $this->repository->save($user);
            $this->auditLogger->logUserChanged($before, $user);
        });
    }
}
