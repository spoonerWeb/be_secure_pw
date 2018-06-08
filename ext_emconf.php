<?php

/**
 * Extension Manager/Repository config file for ext "be_secure_pw".
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 */

$EM_CONF[$_EXTKEY] = [
    'title' => 'Make BE user password really secure',
    'description' => 'You can set password conventions to force secure passwords for BE users.',
    'category' => 'be',
    'shy' => 0,
    'version' => '8.0.1',
    'priority' => '',
    'loadOrder' => '',
    'module' => '',
    'state' => 'stable',
    'createDirs' => '',
    'modify_tables' => '',
    'author' => 'Thomas Loeffler',
    'author_email' => 'loeffler@spooner-web.de',
    'author_company' => '',
    'constraints' => [
        'depends' => [
            'php' => '7.0',
            'typo3' => '7.6.21-9.2.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ]
];
