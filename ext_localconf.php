<?php

use SpoonerWeb\BeSecurePw\Evaluation\PasswordEvaluator;
use SpoonerWeb\BeSecurePw\Form\Element\ForcePasswordChangeButton;
use SpoonerWeb\BeSecurePw\Hook\BackendHook;
use SpoonerWeb\BeSecurePw\Hook\RestrictModulesHook;
use SpoonerWeb\BeSecurePw\Hook\UserSetupHook;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

defined('TYPO3') || die('Access denied.');

$boot = function () {
    // here we register "PasswordEvaluator"
    // for editing by tca form
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals'][PasswordEvaluator::class] = '';

    // Information in user setup module
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/setup/mod/index.php']['modifyUserDataBeforeSave']['be_secure_pw'] =
        UserSetupHook::class . '->modifyUserDataBeforeSave';

    // password reminder
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/backend.php']['constructPostProcess']['be_secure_pw'] =
        BackendHook::class . '->constructPostProcess';

    // Set timestamp for last password change
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['be_secure_pw'] =
        BackendHook::class;

    $extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class)
        ->get('be_secure_pw');

    // execution of is hook only needed in backend, but it is in the abstract class and could also be executed
    // from frontend otherwise if the backend is set to adminOnly, we can not enforce the change,
    // because the hook removes the admin flag
    if (!empty($extConf['forcePasswordChange'])
        && (int)$GLOBALS['TYPO3_CONF_VARS']['BE']['adminOnly'] === 0
    ) {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-postProcess'][] =
            RestrictModulesHook::class . '->addRefreshJavaScript';

        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp'][] =
            RestrictModulesHook::class . '->postUserLookUp';
    }

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1642630971] = [
        'nodeName' => 'forcePasswordChangeButton',
        'priority' => 40,
        'class' => ForcePasswordChangeButton::class,
    ];
};

$boot();
unset($boot);
