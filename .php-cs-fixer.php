<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

return (new Config())
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setFinder(Finder::create()->in([
        './src',
        './tests',
    ]))
    ->setRules([
        '@PSR12' => true,
        '@PHP8x4Migration:risky' => true,
        'php_unit_attributes' => true,
        'no_empty_phpdoc' => true,
        'fully_qualified_strict_types' => [
            'import_symbols' => true,
            'phpdoc_tags' => [],
            'leading_backslash_in_global_namespace' => false,
        ],
        'global_namespace_import' => [
            'import_classes' => false,
            'import_constants' => true,
            'import_functions' => true,
        ],
        'no_unused_imports' => true,
    ])
    ->setRiskyAllowed(true)
    ->setUsingCache(true);
