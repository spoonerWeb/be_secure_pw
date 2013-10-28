<?php
namespace SpoonerWeb\BeSecurePw\Evaluation;

use TYPO3\CMS\Core\Utility;
use TYPO3\CMS\Saltedpasswords\Utility\SaltedPasswordsUtility;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010 Thomas Loeffler <loeffler@spooner-web.de>
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

/**
 * Class PasswordEvaluator
 *
 * @package be_secure_pw
 * @author Thomas Loeffler <loeffler@spooner-web.de>
 */
class PasswordEvaluator {

	/**
	 * This function just return the field value as it is. No transforming,
	 * hashing will be done on server-side.
	 *
	 * @return string JavaScript code for evaluation
	 */
	public function returnFieldJS() {
		return 'return value;';
	}

	/**
	 * Function uses Portable PHP Hashing Framework to create a proper password string if needed
	 *
	 * @param mixed $value The value that has to be checked.
	 * @param string $is_in Is-In String
	 * @param integer $set Determines if the field can be set (value correct) or not, e.g. if input is required but the value is empty, then $set should be set to FALSE. (PASSED BY REFERENCE!)
	 * @param boolean $onlyCheck if activated password will not be md5'ed.
	 * @return string The new value of the field
	 */
	public function evaluateFieldValue($value, $is_in, &$set, $onlyCheck = FALSE) {
		$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['be_secure_pw']);
		$exit = FALSE;
		$noMD5 = FALSE;
		$set = TRUE;

		if (Utility\ExtensionManagementUtility::isLoaded('saltedpasswords')) {
			if (SaltedPasswordsUtility::isUsageEnabled('BE')) {
				$noMD5 = TRUE;
			}
		}

		if ($value && strlen($value) == 32) {
			return $value;
		}

		// check for password length
		$passwordLength = (int) $confArr['passwordLength'];
		if ($confArr['passwordLength'] && $passwordLength) {
			if (strlen($value) < $confArr['passwordLength']) {
				$exit = TRUE;
				$set = FALSE;
			}
		}

		// create tce object for logging
		/** @var \TYPO3\CMS\Core\DataHandling\DataHandler $tce */
		$tce = Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\DataHandling\\DataHandler');
		$tce->BE_USER = $GLOBALS['BE_USER'];

		// get the languages from ext
		/** @var \TYPO3\CMS\Lang\LanguageService $LANG */
		$LANG = Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Lang\\LanguageService');
		$LANG->init($tce->BE_USER->uc['lang']);
		$LANG->includeLLFile('EXT:be_secure_pw/Resources/Private/Language/locallang.xml');

		if (!$exit) {
			$counter = 0;
			$notUsed = array();

			// check for lowercase characters
			if ($confArr['lowercaseChar']) {
				if (preg_match("/[a-z]/", $value) > 0) {
					$counter++;
				} else {
					$notUsed[] = $LANG->getLL('lowercaseChar');
				}
			}

			// check for capital characters
			if ($confArr['capitalChar']) {
				if (preg_match("/[A-Z]/", $value) > 0) {
					$counter++;
				} else {
					$notUsed[] = $LANG->getLL('capitalChar');
				}
			}

			// check for digits
			if ($confArr['digit']) {
				if (preg_match("/[0-9]/", $value) > 0) {
					$counter++;
				} else {
					$notUsed[] = $LANG->getLL('digit');
				}
			}

			// check for special characters
			if ($confArr['specialChar']) {
				if (preg_match("/[^0-9a-z]/i", $value) > 0) {
					$counter++;
				} else {
					$notUsed[] = $LANG->getLL('specialChar');
				}
			}
		}

		if ($exit) {
			/* password too short */
			$tce->log('be_users', 0, 5, 0, 1, $LANG->getLL('shortPassword'), FALSE, array($passwordLength));
			if ($onlyCheck) {
				return array(
					'errorMessage' => 'password_too_short',
					'errorValue' => $passwordLength,
					'notUsed' => array()
				);
			}
		} elseif ($counter < $confArr['patterns']) {
			/* password does not fit all conventions */
			$ignoredPatterns = $confArr['patterns'] - $counter;

			$additional = '';
			$set = FALSE;
			if (is_array($notUsed) && sizeof($notUsed) > 0) {

				if (sizeof($notUsed) > 1) {
					$additional = sprintf($LANG->getLL('notUsedConventions'), implode(', ', $notUsed));
				} else {
					$additional = sprintf($LANG->getLL('notUsedConvention'), $notUsed[0]);
				}
			}

			if ($ignoredPatterns == '1') {
				$tce->log('be_users', 0, 5, 0, 1, $LANG->getLL('passwordConvention') . $additional, FALSE, array($ignoredPatterns));
				if ($onlyCheck) {
					return array(
						'errorMessage' => 'password_no_convention',
						'errorValue' => $ignoredPatterns,
						'notUsed' => $notUsed
					);
				}
			} elseif ($ignoredPatterns > 1) {
				$tce->log('be_users', 0, 5, 0, 1, $LANG->getLL('passwordConvention') . $additional, FALSE, array($ignoredPatterns));
				if ($onlyCheck) {
					return array(
						'errorMessage' => 'password_no_conventions',
						'errorValue' => $ignoredPatterns,
						'notUsed' => $notUsed
					);
				}
			}
		} else {
			/* no problems */
			if ($onlyCheck || $noMD5) {
				return $value;
			} else {
				return md5($value);
			}
		}

		/* if error return old password! */
		return $GLOBALS['BE_USER']->user['password'];
	}
}

?>