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
    'version' => '10.1.0',
    'state' => 'stable',
    'author' => 'Thomas Loeffler',
    'author_email' => 'loeffler@spooner-web.de',
    'author_company' => '',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.0-11.5.99',
            'php' => '7.4.0-8.1.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
