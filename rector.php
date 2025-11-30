<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\ValueObject\PhpVersion;
use Rector\Set\ValueObject\SetList;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Fidry\CpuCoreCounter\CpuCoreCounter;
use Fidry\CpuCoreCounter\Finder\FinderRegistry;
use Rector\DeadCode\Rector\Property\RemoveUselessVarTagRector;
use Rector\Strict\Rector\Empty_\DisallowedEmptyRuleFixerRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveNullTagValueNodeRector;
use Rector\CodeQuality\Rector\BooleanOr\RepeatedOrEqualToInArrayRector;
use Rector\Php84\Rector\Class_\DeprecatedAnnotationToDeprecatedAttributeRector;
use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;

return RectorConfig::configure()
    ->withPhpVersion(PhpVersion::PHP_84)
    ->withParallel(
        512,
        new CpuCoreCounter(FinderRegistry::getDefaultLogicalFinders())->getCount()
    )
    ->withPaths([
        __DIR__ . \DIRECTORY_SEPARATOR . 'src',
        __DIR__ . \DIRECTORY_SEPARATOR . 'tests',
    ])
    ->withSets([
        PHPUnitSetList::ANNOTATIONS_TO_ATTRIBUTES,
        PHPUnitSetList::PHPUNIT_CODE_QUALITY,
        PHPUnitSetList::PHPUNIT_120,

        SetList::CODE_QUALITY,
        SetList::DEAD_CODE,
        SetList::TYPE_DECLARATION,
        SetList::EARLY_RETURN,
    ])
    ->withPhpSets(php84: true)
    ->withSkip([
        DeprecatedAnnotationToDeprecatedAttributeRector::class,
        RepeatedOrEqualToInArrayRector::class,
        FlipTypeControlToUseExclusiveTypeRector::class,
        DisallowedEmptyRuleFixerRector::class,
        RemoveUselessParamTagRector::class,
        RemoveUselessReturnTagRector::class,
        RemoveUselessVarTagRector::class,
        RemoveNullTagValueNodeRector::class,
    ]);
