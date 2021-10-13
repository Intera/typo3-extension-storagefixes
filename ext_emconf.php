<?php

declare(strict_types=1);

/**
 * @var string $_EXTKEY
 */

$EM_CONF[$_EXTKEY] = [
    'title' => 'Storage fixes',
    'description' => 'Adds missing storage features like cross storage directory moving / copying.',
    'category' => 'be',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 1,
    'author' => 'Alexander Stehlik',
    'author_email' => 'astehlik.deleteme@intera.de',
    'author_company' => 'Intera GmbH',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => ['typo3' => '7.6.0-7.6.99'],
        'conflicts' => '',
        'suggests' => [],
    ],
];
