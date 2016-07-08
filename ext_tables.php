<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

// for editing per "user settings"
$version7 = \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger('7.0.0');
$currentVersion = \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version);

if ($currentVersion < $version7) {
    $saltedPasswordEvaluator = 'tx_saltedpasswords_eval_be';
} else {
    $saltedPasswordEvaluator = \TYPO3\CMS\Saltedpasswords\Evaluation\BackendEvaluator::class;
}

/* set password evaluation for password field in be_users */
$evaluation = [
    'required',
    \SpoonerWeb\BeSecurePw\Evaluation\PasswordEvaluator::class,
    $saltedPasswordEvaluator,
    'password'
];
$GLOBALS['TCA']['be_users']['columns']['password']['config']['eval'] = implode(',', $evaluation);

/* override language file */
$TCA_DESCR['_MOD_user_setup']['refs'][] = 'EXT:be_secure_pw/Resources/Private/Language/ux_locallang_csh_mod.xml';
