<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

// here we register "PasswordEvaluator"
// for editing by tca form
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals']['SpoonerWeb\\BeSecurePw\\Evaluation\\PasswordEvaluator'] =
    'EXT:be_secure_pw/Classes/Evaluation/PasswordEvaluator.php';

// for editing per "user settings"
$version7 = \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger('7.0.0');
$currentVersion = \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version);
if ($currentVersion < $version7) {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/template.php']['preStartPageHook']['be_secure_pw'] =
        'SpoonerWeb\\BeSecurePw\\Hook\\UserSetupHook->preStartPageHook';
} else {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/setup/mod/index.php']['setupScriptHook']['be_secure_pw'] =
        'SpoonerWeb\\BeSecurePw\\Hook\\UserSetupHook->setupScriptHook';
}
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/template.php']['moduleBodyPostProcess']['be_secure_pw'] =
    'SpoonerWeb\\BeSecurePw\\Hook\\UserSetupHook->moduleBodyPostProcess';

// password reminder
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/backend.php']['constructPostProcess']['be_secure_pw'] =
    'SpoonerWeb\\BeSecurePw\\Hook\\BackendHook->constructPostProcess';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['be_secure_pw'] =
    'SpoonerWeb\\BeSecurePw\\Hook\\BackendHook';

