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
    'version' => '9.0.0-dev',
    'constraints' => [
        'depends' => ['typo3' => '9.5.0-9.5.99'],
        'conflicts' => '',
        'suggests' => [],
    ],
];
