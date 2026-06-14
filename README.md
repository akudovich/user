# User Management

**Quality**

[![Composer](https://github.com/akudovich/user/actions/workflows/composer-validate.yaml/badge.svg)](https://github.com/akudovich/user/actions/workflows/composer-validate.yaml)
[![Security](https://github.com/akudovich/user/actions/workflows/security-audit.yaml/badge.svg)](https://github.com/akudovich/user/actions/workflows/security-audit.yaml)
[![Code Style](https://github.com/akudovich/user/actions/workflows/code-style.yaml/badge.svg)](https://github.com/akudovich/user/actions/workflows/code-style.yaml)
[![PHPStan](https://github.com/akudovich/user/actions/workflows/phpstan.yaml/badge.svg)](https://github.com/akudovich/user/actions/workflows/phpstan.yaml)
[![Architecture](https://github.com/akudovich/user/actions/workflows/architecture.yaml/badge.svg)](https://github.com/akudovich/user/actions/workflows/architecture.yaml)

**Tests**

[![Tests](https://github.com/akudovich/user/actions/workflows/tests.yaml/badge.svg)](https://github.com/akudovich/user/actions/workflows/tests.yaml)
[![Codecov](https://codecov.io/gh/akudovich/user/branch/master/graph/badge.svg)](https://app.codecov.io/gh/akudovich/user)
[![Infection](https://github.com/akudovich/user/actions/workflows/infection.yaml/badge.svg)](https://github.com/akudovich/user/actions/workflows/infection.yaml)

Test project for managing user accounts stored in a `users` table.

The project implements the domain and application layers for reading, creating,
updating, and soft-deleting users. Real database access, audit storage,
forbidden-word storage, and untrusted-domain storage are intentionally modeled
as ports/interfaces, because the original task allows these parts to be omitted.

## Requirements

- PHP 8.2+
- Composer 2
- Docker and Docker Compose, if you prefer the provided containerized workflow

## Business Rules

`name`:

- contains only `a-z` and `0-9`
- has length from 8 to 64 characters
- must not contain forbidden words
- must be unique

`email`:

- must have a valid email format
- must not belong to an untrusted domain
- must be unique

`deleted`:

- represents soft deletion
- is `null` for active users
- cannot be earlier than `created`

Every change to an existing user is written through the `AuditLogger` port.

## Architecture

The code is split into two main layers:

- `src/Domain` contains the `User` aggregate, value objects, and domain
  exceptions.
- `src/Application` contains use-case handlers, commands, ports, and
  application exceptions.

Main use cases:

- `GetUserHandler`
- `CreateUserHandler`
- `UpdateUserHandler`
- `DeleteUserHandler`

External concerns are represented as interfaces:

- `UserRepository`
- `AuditLogger`
- `TransactionManager`
- `ForbiddenWordsChecker`
- `UntrustedDomainChecker`
- `Clock`

Tests use in-memory doubles from `tests/Double`.

## Installation

With local PHP and Composer:

```bash
composer install
```

With Docker:

```bash
docker compose up -d
docker compose exec -T php composer install
```

## Quality Checks

Run the main checks:

```bash
composer qa
```

Or run checks separately:

```bash
composer cs-check
composer deptrac
composer phpstan
composer phpunit
composer infection
composer security-audit
```

Generate coverage:

```bash
composer coverage
```

Inside Docker, prefix commands with:

```bash
docker compose exec -T php
```

Example:

```bash
docker compose exec -T php composer qa
```

## Development Notes

- There is no framework runtime or HTTP entrypoint in this project.
- The database schema from the task is treated as an external persistence
  concern; production code should provide a concrete `UserRepository`
  implementation.
- Uniqueness should ultimately be enforced by database unique constraints.
  Application-level existence checks are useful for friendly errors, but a real
  persistence adapter must still handle unique-constraint violations. In case of
  a race condition, the `UserRepository` implementation is expected to throw
  `UserAlreadyExists`, which can then be mapped to a user-facing application
  error.
- `notes = null` means "do not update notes" in `UpdateUserCommand`. The
  implementation assumes notes can be cleared by setting them to an empty
  string. If clearing notes to database `NULL` is required, `UpdateUserCommand`
  should get an explicit clear flag so it can distinguish "field was not
  provided" from "clear this field".
- Command handlers assume commands are already validated by the caller. In
  particular, `UpdateUserCommand` is expected to contain at least one non-null
  field besides `id`.
