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
    'version' => '9.0.0',
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
            'php' => '7.2',
            'typo3' => '9.5.7 - 9.5.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ]
];
