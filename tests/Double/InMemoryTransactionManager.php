<?php

declare(strict_types=1);

namespace App\Tests\Double;

use App\Application\TransactionManager;

final readonly class InMemoryTransactionManager implements TransactionManager
{
    public function transactional(callable $callback): mixed
    {
        return $callback();
    }
}
