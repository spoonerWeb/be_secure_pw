<?php
/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

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
            'default' => 0
        ]
    ],
];

ExtensionManagementUtility::addTCAcolumns('be_users', $tempColumns);
ExtensionManagementUtility::addToAllTCAtypes('be_users', 'tx_besecurepw_lastpwchange');

$GLOBALS['TCA']['be_users']['columns']['password']['config']['eval'] = implode(',', [
    'required',
    \SpoonerWeb\BeSecurePw\Evaluation\PasswordEvaluator::class,
    'password'
]);
