<?php

require_once __DIR__ . '/../../vendor/autoload.php';

$finder = PhpCsFixer\Finder::create()
	->exclude(array(
		__DIR__ . '/../../sql',
		__DIR__ . '/../../CI',
	))
	->in([
		__DIR__ .  '/../../classes',
	])
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'strict_param' => false,
        'concat_space' => ['spacing' => 'one'],
        'function_typehint_space' => true
    ])
    ->setFinder($finder);
