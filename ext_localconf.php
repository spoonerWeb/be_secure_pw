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
    // Functionality for user setup module
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/template.php']['preStartPageHook']['be_secure_pw'] =
        'SpoonerWeb\\BeSecurePw\\Hook\\UserSetupHook->preStartPageHook';

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/template.php']['moduleBodyPostProcess']['be_secure_pw'] =
        'SpoonerWeb\\BeSecurePw\\Hook\\UserSetupHook->moduleBodyPostProcess';
}

// Information in user setup module
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/setup/mod/index.php']['modifyUserDataBeforeSave']['be_secure_pw'] =
    'SpoonerWeb\\BeSecurePw\\Hook\\UserSetupHook->modifyUserDataBeforeSave';

// Set timestamp for last password change
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['be_secure_pw'] =
    'SpoonerWeb\\BeSecurePw\\Hook\\BackendHook';

// password reminder
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/backend.php']['constructPostProcess'][] =
    'SpoonerWeb\\BeSecurePw\\Hook\\BackendHook->constructPostProcess';

$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$_EXTKEY]);

// execution of is hook only needed in backend, but it is in the abstract class and could also be executed from frontend otherwise
// if the backend is set to adminOnly, we can not enforce the change, because the hook removes the admin flag
if (!empty($extConf['forcePasswordChange']) && TYPO3_MODE === 'BE' && (int)$TYPO3_CONF_VARS['BE']['adminOnly'] === 0) {

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-postProcess'][] =
        'SpoonerWeb\\BeSecurePw\\Hook\\RestrictModulesHook->addRefreshJavaScript';

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp'][] =
        'SpoonerWeb\\BeSecurePw\\Hook\\RestrictModulesHook->postUserLookUp';
}
