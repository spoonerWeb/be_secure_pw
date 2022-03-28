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
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
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
     * @param array<array> $params
     * @param SetupModuleController $parentObject
     */
    public function modifyUserDataBeforeSave(array &$params, SetupModuleController $parentObject): void
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

        // Prevent same password as before
        if ($params['be_user_data']['password'] === $params['be_user_data']['passwordCurrent']) {
            $params['be_user_data']['password'] = '';
            $params['be_user_data']['password2'] = '';
            $this->getLanguageLabels();
            $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
            /** @var FlashMessageQueue $messageQueue */
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
        if ($params['be_user_data']['password'] === '') {
            $params['be_user_data']['password2'] = '';
        }
    }

    private function getLanguageLabels(): void
    {
        // get the languages from ext
        if (empty($GLOBALS['LANG'])) {
            $GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageService::class);
            $GLOBALS['LANG']->init($GLOBALS['BE_USER']->uc['lang']);
        }
        $GLOBALS['LANG']->includeLLFile('EXT:be_secure_pw/Resources/Private/Language/locallang.xlf');
    }
}
