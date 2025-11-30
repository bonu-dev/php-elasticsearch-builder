<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

return (new Config())
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setFinder(Finder::create()->in([
        __DIR__ . DIRECTORY_SEPARATOR . 'src',
        __DIR__ . DIRECTORY_SEPARATOR . 'scripts',
        __DIR__ . DIRECTORY_SEPARATOR . 'tests',
    ]))
    ->setRules([
        // Ruleset
        '@PHP8x1Migration' => true,
        '@PHP8x2Migration' => true,
        '@PSR1' => true,
        '@PSR2' => true,
        '@PSR12' => true,

        // Control structure
        'yoda_style' => [
            'equal' => false,
            'identical' => false,
            'less_and_greater' => false,
        ],

        // Function notation
        'type_declaration_spaces' => true,
        'lambda_not_used_import' => true,
        'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline'],
        'nullable_type_declaration_for_default_null_value' => true,

        // Phpdoc
        'general_phpdoc_annotation_remove' => ['annotations' => [
            'author',
            'package',
        ]],
        'no_empty_phpdoc' => true,
        'phpdoc_no_empty_return' => true,
        'align_multiline_comment' => ['comment_type' => 'phpdocs_only'],
        'no_blank_lines_after_phpdoc' => true,
        'phpdoc_add_missing_param_annotation' => true,
        'phpdoc_align' => [
            'align' => 'left',
        ],
        'phpdoc_annotation_without_dot' => true,
        'phpdoc_indent' => true,
        'phpdoc_line_span' => [
            'const' => null,
        ],
        'phpdoc_no_package' => true,
        'phpdoc_order' => [
            'order' => [
                'inheritDoc',
                'test',
                'dataProvider',
                'template',
                'comment',
                'param',
                'return',
                'uses',
                'throws',
            ],
        ],
        'phpdoc_scalar' => true,
        'phpdoc_tag_casing' => [
            'tags' => [
                'inheritDoc',
                'todo',
            ],
        ],
        'phpdoc_trim' => true,
        'phpdoc_separation' => [
            'groups' => [
                ['var', 'phpstan-var'],
                ['todo'],
                ['comment'],
                ['param','phpstan-param'],
                ['return','phpstan-return'],
                ['uses'],
                ['throws'],
            ],
        ],
        'phpdoc_param_order' => true,
        'phpdoc_return_self_reference' => true,
        'phpdoc_types_order' => [
            'null_adjustment' => 'always_first',
            'sort_algorithm' => 'alpha',
        ],
        'phpdoc_no_useless_inheritdoc' => true,
        'phpdoc_var_annotation_correct_order' => true,
        'phpdoc_var_without_name' => true,

        // Import
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
        'no_unneeded_import_alias' => true,
        'no_unused_imports' => true,
        'ordered_imports' => [
            'imports_order' => [
                'class', 'function', 'const',
            ],
            'sort_algorithm' => 'length',
        ],
        'single_import_per_statement' => [
            'group_to_single_imports' => true,
        ],

        // Language Construct
        'combine_consecutive_issets' => true,
        'single_space_around_construct' => true,
        'nullable_type_declaration' => [
            'syntax' => 'question_mark',
        ],

        // Semicolon
        'no_singleline_whitespace_before_semicolons' => true,

        // Namespace
        'no_leading_namespace_whitespace' => true,

        // Operator
        'no_useless_nullsafe_operator' => true,
        'not_operator_with_successor_space' => true,
        'object_operator_without_whitespace' => true,
        'operator_linebreak' => ['position' => 'beginning'],
        'standardize_not_equals' => true,
        'unary_operator_spaces' => true,
        'concat_space' => [
            'spacing' => 'one',
        ],
        'new_expression_parentheses' => true,

        // Phpunit
        'php_unit_fqcn_annotation' => true,
        'php_unit_method_casing' => [
            'case' => 'camel_case',
        ],
        'php_unit_internal_class' => true,
        'php_unit_attributes' => true,

        // Class notation
        'self_static_accessor' => true,
        'protected_to_private' => true,
        'class_definition' => true,
        'ordered_class_elements' => [
            'order' => [
                'use_trait',
                'case',
                'constant_public',
                'constant_protected',
                'constant_private',
                'property_public',
                'property_protected',
                'property_private',
                'construct',
                'destruct',
                'magic',
                'method:casts',
                'method_public',
                'method_protected',
                'method_private',
            ],
        ],
        'class_attributes_separation' => [
            'elements' => [
                'method' => 'one',
                'property' => 'one',
                'case' => 'none',
            ],
        ],

        // Attribute notation
        'ordered_attributes' => true,
        'attribute_empty_parentheses' => true,

        'single_quote' => true,
        'declare_strict_types' => true,
        'blank_line_after_opening_tag' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'short_scalar_cast' => true,
        'no_trailing_comma_in_singleline' => true,
        'trailing_comma_in_multiline' => true,
        'trim_array_spaces' => true,
        'native_function_invocation' => ['include' => ['@all']],
        'array_syntax' => ['syntax' => 'short'],
        'psr_autoloading' => ['dir' => './src'],
        'native_function_casing' => true,
        'native_type_declaration_casing' => true,
        'native_constant_invocation' => [
            'fix_built_in' => true,
        ],
        'cast_spaces' => [
            'space' => 'single',
        ],
        'types_spaces' => [
            'space' => 'single',
            'space_multiple_catch' => 'single',
        ],
        'array_indentation' => true,
        'compact_nullable_type_declaration' => true,
        'method_chaining_indentation' => true,
        'statement_indentation' => true,
        'return_to_yield_from' => true,
    ])
    ->setRiskyAllowed(true)
    ->setUsingCache(true);
