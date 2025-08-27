<?php

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/templates/capitalcraft',
    ])
    ->name('*.php')
    ->notPath('vendor')
    ->notPath('node_modules')
    ->notPath('media/vendor')
    ->notPath('media/node_modules');

return (new PhpCsFixer\Config())
    ->setRules([
        // Базовые правила PSR-12
        '@PSR12' => true,
        
        // Дополнительные правила для лучшего форматирования
        'array_syntax' => ['syntax' => 'short'],
        'binary_operator_spaces' => true,
        'concat_space' => ['spacing' => 'one'],
        'no_trailing_whitespace' => true,
        'no_unused_imports' => true,
        'single_quote' => true,
        'trailing_comma_in_multiline' => true,
        
        // Специальные правила для HTML в PHP
        'phpdoc_to_comment' => false, // Отключаем для HTML комментариев
    ])
    ->setFinder($finder)
    ->setRiskyAllowed(true)
    ->setUsingCache(false);
