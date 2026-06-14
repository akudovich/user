<?php

declare(strict_types=1);

namespace App\Tests\Domain;

use App\Domain\Exception\InvalidEmail;
use App\Domain\User\Email;
use PHPUnit\Framework\TestCase;

final class EmailTest extends TestCase
{
    public function testValidEmail(): void
    {
        $email = new Email('test@example.com');

        $this->assertEquals('test@example.com', $email->getValue());
    }

    public function testInvalidEmail(): void
    {
        $this->expectException(InvalidEmail::class);

        new Email('invalid-email');
    }

    public function testDomain(): void
    {
        $email = new Email('test@sub.example.com');

        $this->assertEquals('sub.example.com', $email->getDomain());
    }
}
