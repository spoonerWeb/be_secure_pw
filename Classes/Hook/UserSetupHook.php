<?php
namespace SpoonerWeb\BeSecurePw\Hook;

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

use SpoonerWeb\BeSecurePw\Utilities\PasswordExpirationUtility;
use TYPO3\CMS\Core\Messaging;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility;

/**
 * Class UserSetupHook
 *
 * @package be_secure_pw
 * @author Thomas Loeffler <loeffler@spooner-web.de>
 */
class UserSetupHook
{

    /**
     * checks if the password is not the same as the previous one
     *
     * @param array $newSetup
     * @param \TYPO3\CMS\Setup\Controller\SetupModuleController $parentObj
     */
    public function modifyUserDataBeforeSave(&$newSetup, &$parentObj)
    {
        if ($newSetup['be_user_data']['password'] == '')
            return;

        // only do that, if the record was edited from the user himself
        if ($GLOBALS['BE_USER']->user['ses_backuserid'])
            return;

        $extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['be_secure_pw']);

        if (!$extConf['forcePasswordChange'] || !$extConf['forbidSamePassword'])
            return;

        if (!PasswordExpirationUtility::isBeUserPasswordExpired())
            return;

        $beUserOld = BackendUtility::getRecord('be_users', $GLOBALS['BE_USER']->user['uid']);

        $serviceChain = '';
        $subType = 'authUserBE';
        while (is_object($serviceObj = Utility\GeneralUtility::makeInstanceService('auth', $subType, $serviceChain))) {
            $serviceChain .= ',' . $serviceObj->getServiceKey();
            $serviceObj->initAuth('authGroupsBE', array(), array(), $GLOBALS['BE_USER']);
            syslog(1, get_class($serviceObj));

            if ($serviceObj->compareUident($beUserOld, array(
                    'uident_text' => $newSetup['be_user_data']['password'])
            )
            ) {
                // Password is the same.  Add flash message...
                $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['be_secure_pw']['showPasswordNotChangedMessage'] = true;
                unset($newSetup['be_user_data']['password']);
                unset($newSetup['be_user_data']['password2']);
                return;
            }
        }
    }

    /**
     * @return void
     */
    private function getLanguageLabels()
    {
        // get the languages from ext
        if (empty($GLOBALS['LANG'])) {
            $GLOBALS['LANG'] = Utility\GeneralUtility::makeInstance('language');
            $GLOBALS['LANG']->init($GLOBALS['BE_USER']->uc['lang']);
        }
        $GLOBALS['LANG']->includeLLFile('EXT:be_secure_pw/Resources/Private/Language/locallang.xml');
    }

    /**
     * Add flash message with instructions for user.
     *
     * @param array &$params
     * @param \TYPO3\CMS\Backend\Template\DocumentTemplate &$parentObj
     */
    public function moduleBodyPostProcess(&$params, &$parentObj)
    {
        // execute only in user setup module
        if ($parentObj->scriptID == 'ext/setup/mod/index.php') {
            // don't override existing flash messages
            if (!array_key_exists('FLASHMESSAGES', $params['markers'])) {
                // get configuration of a secure password
                $extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['be_secure_pw']);

                $this->getLanguageLabels();
                // how many parameters have to be checked
                $toCheckParams = array(
                    'lowercaseChar',
                    'capitalChar',
                    'digit',
                    'specialChar'
                );
                $checkParameter = array();
                foreach ($toCheckParams as $parameter) {
                    if ($extConf[$parameter] == 1) {
                        $checkParameter[] = $GLOBALS['LANG']->getLL($parameter);
                    }
                }

                $passwordExpiredNotice = null;
                if ($extConf['forcePasswordChange'] && PasswordExpirationUtility::isBeUserPasswordExpired()) {
                    $passwordExpiredNotice = Utility\GeneralUtility::makeInstance('\\TYPO3\\CMS\\Core\\Messaging\\FlashMessage',
                        $GLOBALS['LANG']->getLL('beSecurePw.passwordExpiredBody'),
                        $GLOBALS['LANG']->getLL('beSecurePw.passwordExpiredHeader'),
                        Messaging\FlashMessage::ERROR
                    );
                }

                // flash message with instructions for the user
                $flashMessage = Utility\GeneralUtility::makeInstance(
                    'TYPO3\CMS\Core\Messaging\FlashMessage',
                    sprintf(
                        $GLOBALS['LANG']->getLL('beSecurePw.description'),
                        $extConf['passwordLength'],
                        implode(', ', $checkParameter),
                        $extConf['patterns']
                    ),
                    $GLOBALS['LANG']->getLL('beSecurePw.header'),
                    Messaging\FlashMessage::INFO,
                    true
                );

                $passwordEqualNotice = null;
                if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['be_secure_pw']['showPasswordNotChangedMessage']) {
                    $passwordEqualNotice = Utility\GeneralUtility::makeInstance('\\TYPO3\\CMS\\Core\\Messaging\\FlashMessage',
                        $GLOBALS['LANG']->getLL('beSecurePw.passwordNotChangedBody'),
                        $GLOBALS['LANG']->getLL('beSecurePw.passwordNotChangedHeader'),
                        Messaging\FlashMessage::ERROR
                    );
                }

                $params['markers']['FLASHMESSAGES'] = '<div id="typo3-messages">' .
                    ($passwordExpiredNotice ? $passwordExpiredNotice->render() : '') .
                    $flashMessage->render() .
                    ($passwordEqualNotice ? $passwordEqualNotice->render() : '') .
                    '</div>';

                // put flash message in front of content
                if (strpos($params['moduleBody'], '###FLASHMESSAGES###') === false) {
                    $params['moduleBody'] = str_replace(
                        '###CONTENT###',
                        '###FLASHMESSAGES######CONTENT###',
                        $params['moduleBody']
                    );
                }
            }
        }
    }

    /**
     * Hook-function: inject additional JS code
     * called in typo3/template.php:template->startPage
     *
     * @param  $params
     * @param  $parentObj
     */
    public function preStartPageHook($params, &$parentObj)
    {
        if ($parentObj->scriptID == 'ext/setup/mod/index.php') { // execute only in user setup module

            // get configuration of a secure password
            $extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['be_secure_pw']);

            // add configuration for JS function in json format
            $parentObj->JScodeArray['be_secure_pw_inline'] = 'var beSecurePwConf = ' . json_encode($extConf);

            // add JS code for password validation
            $parentObj->JScode .= '<script type="text/javascript" src="'
                . $GLOBALS['BACK_PATH'] . '../'
                . Utility\ExtensionManagementUtility::siteRelPath('be_secure_pw')
                . 'Resources/Public/JavaScript/passwordtester.js"></script>';

        }
    }
}
