<?php
namespace SpoonerWeb\BeSecurePw\Utilities;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010 Thomas Loeffler <loeffler@spooner-web.de>
 *  (c) 2012 Andreas Kießling <andreas.kiessling@web.de>
 *  (c) 2014 Christian Plattner <christian.plattner@world-direct.at>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

class PasswordExpirationUtility {
	/**
	 * Check the backend user record, if the password nees updating
	 * Either because it is expired, or the checkbox to change on next login was set
	 *
	 * @static
	 * @return bool FALSE if the password is still valid
	 */
	public function isBeUserPasswordExpired() {
			// If ses_backuserid is set, an admin switched to that user. He should not be forced to change the password
		if ($GLOBALS['BE_USER']->user['ses_backuserid']) {
			return FALSE;
		}

			// exit, if cli user is found
		if (GeneralUtility::isFirstPartOfStr($GLOBALS['BE_USER']->user['username'], '_cli')) {
			return FALSE;
		}

		// checkbox in user record is set
		if ($GLOBALS['BE_USER']->user['tx_besecurepw_forcepwchange']) {
			return TRUE;
		}

		// if the user just updated his password, $GLOBALS['BE_USER'] record may still hold the old data
		$beUser = BackendUtility::getRecord('be_users', $GLOBALS['BE_USER']->user['uid']);

			// password is too old
		$lastPwChange = $beUser['tx_besecurepw_lastpwchange'];
		$lastLogin = $beUser['lastlogin'];

			// get configuration of a secure password
		$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['be_secure_pw']);

		$validUntilConfiguration = trim($extConf['validUntil']);

		$validUntil = 0;
		if ($validUntilConfiguration != '') {
			$validUntil = strtotime('- '.$validUntilConfiguration);
		}

		return (($validUntilConfiguration != '' && ($lastPwChange == 0 || $lastPwChange < $validUntil)) || $lastLogin == 0);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS['BE']['XCLASS']['ext/be_secure_pw/lib/class.tx_besecurepw_checkBeUserRecord.php']) {
	include_once($TYPO3_CONF_VARS['BE']['XCLASS']['ext/be_secure_pw/lib/class.tx_besecurepw_checkBeUserRecord.php']);
}
