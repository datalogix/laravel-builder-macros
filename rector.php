<?php

use Rector\Config\RectorConfig;
use Rector\Php71\Rector\FuncCall\RemoveExtraParametersRector;
use Rector\TypeDeclaration\Rector\StmtsAwareInterface\SafeDeclareStrictTypesRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    ->withPhpSets()
    ->withPreparedSets(deadCode: true, codeQuality: true, earlyReturn: true)
    ->withSkip([
        SafeDeclareStrictTypesRector::class,
        // Rector resolves whereLike() to Laravel's native method, which has fewer parameters than the macro
        RemoveExtraParametersRector::class => [__DIR__.'/tests/Macros/WhereLikeTest.php'],
    ]);
