<?php

declare(strict_types=1);

namespace SpoonerWeb\BeSecurePw\Hook;

/**
 * This file is part of the be_secure_pw project.
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
use TYPO3\CMS\Backend\Controller\BackendController;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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

        $GLOBALS['LANG']->includeLLFile('EXT:be_secure_pw/Resources/Private/Language/locallang.xlf');

        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $messageQueue = $flashMessageService->getMessageQueueByIdentifier();
        $messageQueue->addMessage(
            new FlashMessage(
                $GLOBALS['LANG']->getLL('needPasswordChange.message'),
                $GLOBALS['LANG']->getLL('needPasswordChange.title'),
                FlashMessage::INFO,
                true
            )
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
                $incomingFieldArray['tx_besecurepw_lastpwchange'] = time() + (int)date('Z');
            }

            // trigger reload of the backend, if it was previously locked down
            if (PasswordExpirationUtility::isBeUserPasswordExpired()) {
                static::$insertModuleRefreshJS = true;
            }
        }
    }
}
