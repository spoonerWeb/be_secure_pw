<?php
namespace SpoonerWeb\BeSecurePw\Evaluation;

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

use TYPO3\CMS\Core\Utility;
use TYPO3\CMS\Saltedpasswords\Utility\SaltedPasswordsUtility;

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
	 * @return string The new value of the field
	 */
	public function evaluateFieldValue($value, $is_in, &$set) {
		$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['be_secure_pw']);

		// create tce object for logging
		/** @var \TYPO3\CMS\Core\DataHandling\DataHandler $tce */
		$tce = Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\DataHandling\\DataHandler');
		$tce->BE_USER = $GLOBALS['BE_USER'];

		// get the languages from ext
		/** @var \TYPO3\CMS\Lang\LanguageService $languageService */
		$languageService = Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Lang\\LanguageService');
		$languageService->init($tce->BE_USER->uc['lang']);
		$languageService->includeLLFile('EXT:be_secure_pw/Resources/Private/Language/locallang.xml');

		/** @var boolean $noMD5 return variable as md5 hash if saltedpasswords isn't enabled */
		$noMD5 = FALSE;
		$set = TRUE;

		if (Utility\ExtensionManagementUtility::isLoaded('saltedpasswords')) {
			if (SaltedPasswordsUtility::isUsageEnabled('BE')) {
				$noMD5 = TRUE;
			}
		}

		$isMD5 = preg_match('/[0-9abcdef]{32,32}/', $value);
		// if $value is a md5 hash, return the value directly
		if ($isMD5 && $noMD5) {
			return $value;
		}

		// check for password length
		$passwordLength = (int) $confArr['passwordLength'];
		if ($confArr['passwordLength'] && $passwordLength) {
			if (strlen($value) < $confArr['passwordLength']) {
				$set = FALSE;
				/* password too short */
				$tce->log(
					'be_users',
					0,
					5,
					0,
					1,
					$languageService->getLL('shortPassword'),
					FALSE,
					array($passwordLength)
				);
			}
		}

		$counter = 0;
		$notUsed = array();

		// check for lowercase characters
		if ($confArr['lowercaseChar']) {
			if (preg_match("/[a-z]/", $value) > 0) {
				$counter++;
			} else {
				$notUsed[] = $languageService->getLL('lowercaseChar');
			}
		}

		// check for capital characters
		if ($confArr['capitalChar']) {
			if (preg_match("/[A-Z]/", $value) > 0) {
				$counter++;
			} else {
				$notUsed[] = $languageService->getLL('capitalChar');
			}
		}

		// check for digits
		if ($confArr['digit']) {
			if (preg_match("/[0-9]/", $value) > 0) {
				$counter++;
			} else {
				$notUsed[] = $languageService->getLL('digit');
			}
		}

		// check for special characters
		if ($confArr['specialChar']) {
			if (preg_match("/[^0-9a-z]/i", $value) > 0) {
				$counter++;
			} else {
				$notUsed[] = $languageService->getLL('specialChar');
			}
		}

		if ($counter < $confArr['patterns']) {
			/* password does not fit all conventions */
			$ignoredPatterns = $confArr['patterns'] - $counter;

			$additional = '';
			$set = FALSE;

			if (is_array($notUsed) && sizeof($notUsed) > 0) {
				if (sizeof($notUsed) > 1) {
					$additional = sprintf($languageService->getLL('notUsedConventions'), implode(', ', $notUsed));
				} else {
					$additional = sprintf($languageService->getLL('notUsedConvention'), $notUsed[0]);
				}
			}

			if ($ignoredPatterns >= 1) {
				$tce->log(
					'be_users',
					0,
					5,
					0,
					1,
					$languageService->getLL('passwordConvention') . $additional,
					FALSE,
					array($ignoredPatterns)
				);
			}
		}

		/* no problems */
		if ($set) {
			if ($noMD5) {
				return $value;
			}

			return md5($value);
		}

		// if password not valid return empty password
		return '';
	}
}

?>