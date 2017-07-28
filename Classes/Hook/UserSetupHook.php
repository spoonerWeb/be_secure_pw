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
use TYPO3\CMS\Core\Utility;
use TYPO3\CMS\Setup\Controller\SetupModuleController;
use SpoonerWeb\BeSecurePw\Evaluation\PasswordEvaluator;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Backend\Template\DocumentTemplate;

/**
 * Class UserSetupHook
 *
 * @author Thomas Loeffler <loeffler@spooner-web.de>
 */
class UserSetupHook
{

    /**
     * checks if the password is not the same as the previous one
     *
     * @param array $params
     * @param \TYPO3\CMS\Setup\Controller\SetupModuleController $parentObject
     */
    public function modifyUserDataBeforeSave(array &$params, SetupModuleController &$parentObject)
    {
        // No new password given then we don't need to preform the checks
        if (empty($params['be_user_data']['password'])
            &&
            empty($params['be_user_data']['password2'])
            &&
            !PasswordExpirationUtility::isBeUserPasswordExpired()
        ) {
            return;
        }

        // Check if password is valid
        $passwordEvaluator = GeneralUtility::makeInstance(PasswordEvaluator::class);
        $set = false;
        $password = $passwordEvaluator->evaluateFieldValue($params['be_user_data']['password'], '', $set);

        // Prevent same password as before
        if ($params['be_user_data']['password'] === $params['be_user_data']['passwordCurrent']) {
            $params['be_user_data']['password'] = '';
            $params['be_user_data']['password2'] = '';
            $this->getLanguageLabels();
            /** @var \TYPO3\CMS\Core\Messaging\FlashMessageQueue $messageQueue */
            $messageQueue = Utility\GeneralUtility::makeInstance(
                Messaging\FlashMessageQueue::class,
                'core.template.flashMessages'
            );
            $messageQueue->addMessage(
                new Messaging\FlashMessage(
                    $GLOBALS['LANG']->getLL('samePassword'),
                    '',
                    Messaging\FlashMessage::WARNING,
                    true
                )
            );
        }

        // Password is not valid, so reset the new passwords to prevent save
        if ($password === '' && $set === false) {
            $params['be_user_data']['password'] = '';
            $params['be_user_data']['password2'] = '';
        }
    }

    /**
     * Add flash message with instructions for user.
     *
     * @param array &$params
     * @param \TYPO3\CMS\Backend\Template\DocumentTemplate &$parentObj
     */
    public function moduleBodyPostProcess(array &$params, DocumentTemplate &$parentObj)
    {
        // execute only in user setup module
        if ($parentObj->scriptID == 'ext/setup/mod/index.php'
            && !array_key_exists('FLASHMESSAGES', $params['markers'])) {
            // don't override existing flash messages
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

            // flash message with instructions for the user
            $flashMessage = Utility\GeneralUtility::makeInstance(
                Messaging\FlashMessage::class,
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

            $params['markers']['FLASHMESSAGES'] = '<div id="typo3-messages">' . $flashMessage->render() . '</div>';

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

    /**
     * Hook-function: inject additional JS code
     * called in typo3/template.php:template->startPage
     *
     * @param array $params
     * @param \TYPO3\CMS\Backend\Template\DocumentTemplate $parentObj
     */
    public function preStartPageHook(array $params, DocumentTemplate &$parentObj)
    {
        // execute only in user setup module
        if ($parentObj->scriptID === 'ext/setup/mod/index.php') {
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
}
