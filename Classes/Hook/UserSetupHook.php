<?php

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

use SpoonerWeb\BeSecurePw\Evaluation\PasswordEvaluator;
use SpoonerWeb\BeSecurePw\Utilities\PasswordExpirationUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Setup\Controller\SetupModuleController;

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
    public function modifyUserDataBeforeSave(array &$params, SetupModuleController $parentObject)
    {
        // No new password given then we don't need to run the checks
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
        $set = 0;
        $password = $passwordEvaluator->evaluateFieldValue($params['be_user_data']['password'], '', $set);

        // Prevent same password as before
        if ($params['be_user_data']['password'] === $params['be_user_data']['passwordCurrent']) {
            $params['be_user_data']['password'] = '';
            $params['be_user_data']['password2'] = '';
            $this->getLanguageLabels();
            /** @var \TYPO3\CMS\Core\Messaging\FlashMessageQueue $messageQueue */
            $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
            $messageQueue = $flashMessageService->getMessageQueueByIdentifier();
            $messageQueue->addMessage(
                new FlashMessage(
                    $GLOBALS['LANG']->getLL('samePassword'),
                    '',
                    FlashMessage::WARNING,
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

    private function getLanguageLabels()
    {
        // get the languages from ext
        if (empty($GLOBALS['LANG'])) {
            $GLOBALS['LANG'] = GeneralUtility::makeInstance('language');
            $GLOBALS['LANG']->init($GLOBALS['BE_USER']->uc['lang']);
        }
        $GLOBALS['LANG']->includeLLFile('EXT:be_secure_pw/Resources/Private/Language/locallang.xlf');
    }
}
