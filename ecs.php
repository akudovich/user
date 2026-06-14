<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Basic\SingleLineEmptyBodyFixer;
use PhpCsFixer\Fixer\ClassNotation\FinalClassFixer;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use Symplify\CodingStandard\Fixer\Spacing\MethodChainingNewlineFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withCache(
        directory: __DIR__ . '/var/ecs'
    )

    // add a single rule
    ->withRules([
        NoUnusedImportsFixer::class,
        SingleLineEmptyBodyFixer::class,
        DeclareStrictTypesFixer::class,
    ])

    // add sets - group of rules, from easiest to more complex ones
    // uncomment one, apply one, commit, PR, merge and repeat
    ->withPreparedSets(
        psr12: true,
        common: true,
        symplify: true,
        cleanCode: true,
    )
    ->withSkip([
        MethodChainingNewlineFixer::class,
    ])
    ;
