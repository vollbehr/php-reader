<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
    ->name('*.php')
    ->ignoreVCS(true)
    ->ignoreDotFiles(false);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'binary_operator_spaces' => [
            'operators' => ['=' => 'align_single_space_minimal'],
        ],
        'blank_line_before_statement' => [
            'statements' => ['return', 'throw'],
        ],
        'declare_strict_types' => true,
        'fully_qualified_strict_types' => true,
        'function_declaration' => ['closure_function_spacing' => 'none'],
        'global_namespace_import' => [
            'import_constants' => true,
            'import_functions' => true,
        ],
        'modernize_strpos' => true,
        'native_constant_invocation' => false,
        'native_function_invocation' => false,
        'no_blank_lines_after_phpdoc' => true,
        'no_superfluous_phpdoc_tags' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'phpdoc_align' => false,
        'phpdoc_line_span' => [
            'property' => 'single',
        ],
        'phpdoc_order' => true,
        'phpdoc_to_comment' => false,
        'single_import_per_statement' => true,
        'single_quote' => true,
        'trailing_comma_in_multiline' => ['elements' => ['arrays']],
        'types_spaces' => ['space' => 'single'],
    ])
    ->setFinder($finder);
