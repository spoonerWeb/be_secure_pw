<?php

declare(strict_types=1);

namespace SpoonerWeb\BeSecurePw\Database\Event;

use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Recordlist\Event\ModifyRecordListRecordActionsEvent;

/**
 * This file is part of the TYPO3 CMS extension "be_secure_pw".
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
class AddForceResetPasswordLinkEvent
{
    public static string $passwordChangeCommand = 'force_password_change';

    public function __invoke(ModifyRecordListRecordActionsEvent $event): void
    {
        if ($event->getTable() === 'be_users') {
            $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
            $url = (string)$uriBuilder->buildUriFromRoutePath(
                '/user/force_password_change',
                [
                    self::$passwordChangeCommand => $event->getRecord()['uid'],
                ]
            );

            $languageService = GeneralUtility::makeInstance(LanguageServiceFactory::class)
                ->createFromUserPreferences($GLOBALS['BE_USER']);
            $languageService->includeLLFile('EXT:be_secure_pw/Resources/Private/Language/locallang.xlf');

            $iconFactory = GeneralUtility::makeInstance(IconFactory::class);
            $forcePasswordChangeAction = '<a class="btn btn-default"'
                . ' href="' . htmlspecialchars($url) . '"'
                . ' title="' . htmlspecialchars($languageService->getLL('forcePasswordChange')) . '">'
                . $iconFactory->getIcon('form-password', Icon::SIZE_SMALL)->render() . '</a>';

            $event->setAction(
                $forcePasswordChangeAction,
                'forcePasswordChange',
                'secondary',
                '',
                'history'
            );
        }
    }
}
