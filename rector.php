<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . DIRECTORY_SEPARATOR . 'src',
        __DIR__ . DIRECTORY_SEPARATOR . 'scripts',
        __DIR__ . DIRECTORY_SEPARATOR . 'tests',
    ])
    ->withPhpSets(php84: true)
    ->withCodeQualityLevel(10)
    ->withDeadCodeLevel(10);
