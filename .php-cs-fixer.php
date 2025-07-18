<?php

$config = new PhpCsFixer\Config();

return $config
    ->setRules([
        '@PSR12' => true,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->exclude('vendor')
            ->exclude('node_modules')
            ->exclude('storage')
            ->exclude('bootstrap/cache')
            ->in(__DIR__)
    );
