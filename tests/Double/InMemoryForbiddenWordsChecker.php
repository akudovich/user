<?php

declare(strict_types=1);

namespace App\Tests\Double;

use App\Application\ForbiddenWordsChecker;
use App\Domain\User\UserName;

final readonly class InMemoryForbiddenWordsChecker implements ForbiddenWordsChecker
{
    private string $regex;

    /**
     * @param iterable<string> $forbiddenWords
     */
    public function __construct(
        iterable $forbiddenWords,
    ) {
        $this->regex = $this->compileForbiddenWordsRegex($forbiddenWords);
    }

    public function isUserNameForbidden(UserName $name): bool
    {
        return preg_match($this->regex, $name->getValue()) === 1;
    }

    /**
     * @param iterable<string> $forbiddenWords
     */
    private function compileForbiddenWordsRegex(iterable $forbiddenWords): string
    {
        // Compile the forbidden words into a regular expression for efficient checking
        // Cache the compiled regex to avoid recompiling it on every call
        $regex = '/(';
        foreach ($forbiddenWords as $word) {
            $escapedWord = preg_quote($word, '/');
            $regex .= $escapedWord . '|';
        }
        return rtrim($regex, '|') . ')/i';
    }
}
