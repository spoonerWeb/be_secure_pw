<?php

defined('TYPO3') || die('Access denied.');

$boot = function () {

    // here we register "PasswordEvaluator"
    // for editing by tca form
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals'][\SpoonerWeb\BeSecurePw\Evaluation\PasswordEvaluator::class] = '';

    // Information in user setup module
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/setup/mod/index.php']['modifyUserDataBeforeSave']['be_secure_pw'] =
        \SpoonerWeb\BeSecurePw\Hook\UserSetupHook::class . '->modifyUserDataBeforeSave';

    // password reminder
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/backend.php']['constructPostProcess']['be_secure_pw'] =
        \SpoonerWeb\BeSecurePw\Hook\BackendHook::class . '->constructPostProcess';

    // Set timestamp for last password change
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['be_secure_pw'] =
        \SpoonerWeb\BeSecurePw\Hook\BackendHook::class;

    $extConf = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class)
        ->get('be_secure_pw');

    // execution of is hook only needed in backend, but it is in the abstract class and could also be executed
    // from frontend otherwise if the backend is set to adminOnly, we can not enforce the change,
    // because the hook removes the admin flag
    if (!empty($extConf['forcePasswordChange'])
        && (int)$GLOBALS['TYPO3_CONF_VARS']['BE']['adminOnly'] === 0
    ) {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-postProcess'][] =
            \SpoonerWeb\BeSecurePw\Hook\RestrictModulesHook::class . '->addRefreshJavaScript';

        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp'][] =
            \SpoonerWeb\BeSecurePw\Hook\RestrictModulesHook::class . '->postUserLookUp';
    }

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1642630971] = [
        'nodeName' => 'forcePasswordChangeButton',
        'priority' => 40,
        'class' => \SpoonerWeb\BeSecurePw\Form\Element\ForcePasswordChangeButton::class
    ];
};

$boot();
unset($boot);
