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
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Backend\Controller\BackendController;
use TYPO3\CMS\Core\DataHandling\DataHandler;

/**
 * Class BackendHook
 *
 * @author Thomas Loeffler <loeffler@spooner-web.de>
 */
class BackendHook
{
    /**
     * @var bool
     */
    public static $insertModuleRefreshJS = false;

    /**
     * reference back to the backend
     *
     * @var \TYPO3\CMS\Backend\Controller\BackendController
     */
    protected $backendReference;

    /**
     * constructPostProcess
     *
     * @param array $config
     * @param \TYPO3\CMS\Backend\Controller\BackendController $backendReference
     */
    public function constructPostProcess(array $config, BackendController $backendReference)
    {
        if (!PasswordExpirationUtility::isBeUserPasswordExpired()) {
            return;
        }

        // let the popup pop up :)
        $ll = 'LLL:EXT:be_secure_pw/Resources/Private/Language/locallang_reminder.xml:';
        $generatedLabels = [
            'passwordReminderWindow_title' => $GLOBALS['LANG']->sL(
                $ll . 'passwordReminderWindow_title'
            ),
            'passwordReminderWindow_message' => $GLOBALS['LANG']->sL(
                $ll . 'passwordReminderWindow_message'
            ),
            'passwordReminderWindow_confirmation' => $GLOBALS['LANG']->sL(
                $ll . 'passwordReminderWindow_confirmation'
            ),
            'passwordReminderWindow_button_changePassword' => $GLOBALS['LANG']->sL(
                $ll . 'passwordReminderWindow_button_changePassword'
            ),
            'passwordReminderWindow_button_postpone' => $GLOBALS['LANG']->sL(
                $ll . 'passwordReminderWindow_button_postpone'
            ),
        ];

        // get configuration of a secure password
        $extConf = unserialize(
            $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['be_secure_pw'],
            ['allowed_classes' => false]
        );

        // Convert labels/settings back to UTF-8 since json_encode() only works with UTF-8:
        if ($GLOBALS['LANG']->charSet !== 'utf-8') {
            $GLOBALS['LANG']->csConvObj->convArray($generatedLabels, $GLOBALS['LANG']->charSet, 'utf-8');
        }

        $labelsForJS = 'TYPO3.LLL.beSecurePw = ' . json_encode($generatedLabels) . ';';

        /** @var PageRenderer $pageRenderer */
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->addJsInlineCode('labels', $labelsForJS);
        $pageRenderer->loadRequireJsModule(
            'TYPO3/CMS/BeSecurePw/Reminder',
            'function(reminder){
                reminder.initModal(' . (!empty($extConf['forcePasswordChange']) ? 'true' : 'false') . ');
            }'
        );
    }


    /**
     * looks for a password change and sets the field "tx_besecurepw_lastpwchange" with an actual timestamp
     *
     * @param array $incomingFieldArray
     * @param string $table
     * @param int $id
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $parentObj
     */
    // @codingStandardsIgnoreLine
    public function processDatamap_preProcessFieldArray(&$incomingFieldArray, $table, $id, DataHandler $parentObj)
    {
        if ($table === 'be_users' && !empty($incomingFieldArray['password'])) {
            // only do that, if the record was edited from the user himself
            if ((int)$id === (int)$GLOBALS['BE_USER']->user['uid']
                && empty($GLOBALS['BE_USER']->user['ses_backuserid'])) {
                $incomingFieldArray['tx_besecurepw_lastpwchange'] = time() + date('Z');
            }

            // trigger reload of the backend, if it was previously locked down
            if (PasswordExpirationUtility::isBeUserPasswordExpired()) {
                static::$insertModuleRefreshJS = true;
            }
        }
    }
}
