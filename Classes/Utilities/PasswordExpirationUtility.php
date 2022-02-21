<?php

declare(strict_types=1);

namespace SpoonerWeb\BeSecurePw\Utilities;

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

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class PasswordExpirationUtility
 *
 * @author Thomas Loeffler <loeffler@spooner-web.de>
 * @author Andreas Kießling <andreas.kiessling@web.de>
 * @author Christian Plattner <christian.plattner@world-direct.at>
 */
class PasswordExpirationUtility
{
    /**
     * Check the backend user record, if the password nees updating
     * Either because it is expired, or the checkbox to change on next login was set
     *
     * @static
     * @return bool FALSE if the password is still valid
     */
    public static function isBeUserPasswordExpired(): bool
    {
        // If ses_backuserid is set, an admin switched to that user. He should not be forced to change the password
        if ($GLOBALS['BE_USER']->getOriginalUserIdWhenInSwitchUserMode()) {
            return false;
        }

        // exit, if cli user is found
        if (strpos($GLOBALS['BE_USER']->user['username'], '_cli') === 0) {
            return false;
        }

        // if the user just updated his password, $GLOBALS['BE_USER'] record may still hold the old data
        $beUser = BackendUtility::getRecord('be_users', (int)$GLOBALS['BE_USER']->user['uid']);

        // password is too old
        $lastPwChange = (int)$beUser['tx_besecurepw_lastpwchange'];
        $lastLogin = (int)$beUser['lastlogin'];

        // get configuration of a secure password
        $extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('be_secure_pw');

        // check if user passwords of admins do not expire
        if ($extConf['ignorePasswordChangeForAdmins'] && $GLOBALS['BE_USER']->isAdmin()) {
            return false;
        }

        $validUntilConfiguration = trim($extConf['validUntil']);

        $validUntil = 0;
        if ($validUntilConfiguration !== '') {
            $validUntil = strtotime('- ' . $validUntilConfiguration);
        }

        return
            (
                $validUntilConfiguration !== ''
                &&
                ($lastPwChange === 0 || $lastPwChange < $validUntil)
            )
            || $lastLogin === 0
        ;
    }
}
