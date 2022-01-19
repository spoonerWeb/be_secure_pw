<?php

use SpoonerWeb\BeSecurePw\Evaluation\PasswordEvaluator;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$tempColumns = [
    'tx_besecurepw_lastpwchange' => [
        'exclude' => 0,
        'label' => 'LLL:EXT:be_secure_pw/Resources/Private/Language/locallang.xml:be_users.tx_besecurepw_lastpwchange',
        'config' => [
            'type' => 'input',
            'size' => 12,
            'eval' => 'datetime',
            'renderType' => 'inputDateTime',
            'default' => 0,
        ],
    ],
];

ExtensionManagementUtility::addTCAcolumns('be_users', $tempColumns);
ExtensionManagementUtility::addToAllTCAtypes('be_users', 'tx_besecurepw_lastpwchange');

$GLOBALS['TCA']['be_users']['columns']['password']['config']['eval'] = str_replace(
    ',saltedPassword',
    ',' . PasswordEvaluator::class . ',saltedPassword',
    $GLOBALS['TCA']['be_users']['columns']['password']['config']['eval']
);
