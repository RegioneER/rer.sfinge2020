<?php

$config = new PhpCsFixer\Config();
$config->setRules([
    '@Symfony' => true,
    '@DoctrineAnnotation' => true,
    'array_syntax' => [
        'syntax' => 'short',
    ],
    'array_indentation' => true,
    'combine_consecutive_unsets' => true,
    // 'method_separation' => true,
    // 'no_multiline_whitespace_before_semicolons' => true,
    'single_quote' => false,
    // 'binary_operator_spaces' => [
        // 'align_double_arrow' => false,
        // 'align_equals' => false,
    // ],
    'braces' => [
        'allow_single_line_closure' => true,
        'position_after_functions_and_oop_constructs' => 'same',
        'allow_single_line_anonymous_class_with_empty_body' => true,
        'position_after_control_structures' => 'same',
        'position_after_anonymous_constructs' => 'same'
    ],
    'cast_spaces' => true,
    'class_definition' => [
        'single_line' => true,
    ],
    'concat_space' => [
        'spacing' => 'one',
    ],
    'declare_equal_normalize' => true,
    'function_typehint_space' => true,
    // 'hash_to_slash_comment' => true,
    'include' => true,
    'lowercase_cast' => true,
    // 'no_extra_consecutive_blank_lines' => [
    //     'curly_brace_block',
    //     'extra',
    //     'parenthesis_brace_block',
    //     'square_brace_block',
    //     'throw',
    //     'use',
    // ],
    'no_leading_import_slash' => true,
    'no_leading_namespace_whitespace' => true,
    'no_multiline_whitespace_around_double_arrow' => true,
    'no_spaces_around_offset' => true,
    'no_trailing_comma_in_list_call' => false,
    'no_trailing_comma_in_singleline_array' => false,
    'no_whitespace_before_comma_in_array' => true,
    'no_whitespace_in_blank_line' => true,
    'object_operator_without_whitespace' => true,
    'phpdoc_align' => false,
    'phpdoc_annotation_without_dot' => true,
    'phpdoc_indent' => true,
    'phpdoc_separation' => false,
    'phpdoc_summary' => false,
    'phpdoc_types' => true,
    'phpdoc_var_without_name' => true,
    // 'no_superfluous_phpdoc_tags' => true,
    'phpdoc_add_missing_param_annotation' => true,
    'phpdoc_scalar' => true,
    'return_type_declaration' => true,
    'single_blank_line_before_namespace' => true,
    'single_class_element_per_statement' => true,
    'space_after_semicolon' => false,
    'ternary_operator_spaces' => true,
    'trailing_comma_in_multiline' => true,
    'trim_array_spaces' => true,
    'unary_operator_spaces' => true,
    'whitespace_after_comma_in_array' => true,
    'blank_line_before_statement' => false,
    'doctrine_annotation_spaces' => true,
    'normalize_index_brace' => true,
    'function_declaration' => [
        'closure_function_spacing' => 'one',
    ],
    'no_spaces_after_function_name' => true,

    ])
    ->setLineEnding("\n");

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
;

$config->setFinder($finder);


return $config;