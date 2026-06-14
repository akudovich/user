<?php

declare(strict_types=1);

namespace App\Tests\Domain;

use App\Domain\Exception\InvalidUserName;
use App\Domain\User\UserName;
use PHPUnit\Framework\TestCase;

final class UserNameTest extends TestCase
{
    public function testValidUserName(): void
    {
        $userName = new UserName('johndoe8');
        $this->assertEquals('johndoe8', $userName->getValue());
    }

    public function testEmptyUserName(): void
    {
        $this->expectException(InvalidUserName::class);
        new UserName('');
    }

    public function testTooShortUserName(): void
    {
        $this->expectException(InvalidUserName::class);
        new UserName('johndoe');
    }

    public function testTooLongUserName(): void
    {
        $this->expectException(InvalidUserName::class);
        new UserName(str_repeat('a', 65));
    }

    public function testMaxLengthUserName(): void
    {
        $maxLengthName = str_repeat('a', 64);
        $userName = new UserName($maxLengthName);
        $this->assertEquals($maxLengthName, $userName->getValue());
    }

    public function testInvalidCharactersStarts(): void
    {
        $this->expectException(InvalidUserName::class);
        new UserName('$Johndoe8');
    }

    public function testInvalidCharactersEnds(): void
    {
        $this->expectException(InvalidUserName::class);
        new UserName('johndoE#');
    }

    public function testInvalidCharactersMiddle(): void
    {
        $this->expectException(InvalidUserName::class);
        new UserName('john^Doe8');
    }
}
