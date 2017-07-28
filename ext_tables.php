<?php
defined('TYPO3_MODE') || die('Access denied.');

$boot = function() {
    // for editing per "user settings"
    $saltedPasswordEvaluator = \TYPO3\CMS\Saltedpasswords\Evaluation\BackendEvaluator::class;

    // set password evaluation for password field in be_users
    $evaluation = [
        'required',
        \SpoonerWeb\BeSecurePw\Evaluation\PasswordEvaluator::class,
        $saltedPasswordEvaluator,
        'password'
    ];
    $GLOBALS['TCA']['be_users']['columns']['password']['config']['eval'] = implode(',', $evaluation);

    /* override language file */
    $GLOBALS['TCA_DESCR']['_MOD_user_setup']['refs'][] =
        'EXT:be_secure_pw/Resources/Private/Language/ux_locallang_csh_mod.xml';
};

$boot();
unset($boot);
