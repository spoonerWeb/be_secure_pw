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

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Class BackendHook
 *
 * @package be_secure_pw
 * @author Thomas Loeffler <loeffler@spooner-web.de>
 */
class BackendHook {

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
	public function constructPostProcess($config, &$backendReference) {
		$lastPwChange = $GLOBALS['BE_USER']->user['tx_besecurepw_lastpwchange'];
		$lastLogin = $GLOBALS['BE_USER']->user['lastlogin'];

		// get configuration of a secure password
		$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['be_secure_pw']);

		$validUntilConfiguration = trim($extConf['validUntil']);

		$validUntil = 0;
		if ($validUntilConfiguration != '') {
			$validUntil = strtotime('- ' . $validUntilConfiguration);
		}

		if (($validUntilConfiguration != '' && ($lastPwChange == 0 || $lastPwChange < $validUntil)) || $lastLogin == 0) {
			// let the popup pop up :)
			$generatedLabels = array(
				'passwordReminderWindow_title' => $GLOBALS['LANG']->sL(
					'LLL:EXT:be_secure_pw/Resources/Private/Language/locallang_reminder.xml:passwordReminderWindow_title'
				),
				'passwordReminderWindow_message' => $GLOBALS['LANG']->sL(
					'LLL:EXT:be_secure_pw/Resources/Private/Language/locallang_reminder.xml:passwordReminderWindow_message'
				),
				'passwordReminderWindow_button_changePassword' => $GLOBALS['LANG']->sL(
					'LLL:EXT:be_secure_pw/Resources/Private/Language/locallang_reminder.xml:passwordReminderWindow_button_changePassword'
				),
				'passwordReminderWindow_button_postpone' => $GLOBALS['LANG']->sL(
					'LLL:EXT:be_secure_pw/Resources/Private/Language/locallang_reminder.xml:passwordReminderWindow_button_postpone'
				),
			);

			// Convert labels/settings back to UTF-8 since json_encode() only works with UTF-8:
			if ($GLOBALS['LANG']->charSet !== 'utf-8') {
				$GLOBALS['LANG']->csConvObj->convArray($generatedLabels, $GLOBALS['LANG']->charSet, 'utf-8');
			}

			$labelsForJS = 'TYPO3.LLL.beSecurePw = ' . json_encode($generatedLabels) . ';';

			$backendReference->addJavascript($labelsForJS);
			$version7 = \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger('7.0.0');
			$currentVersion = \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version);
			if ($currentVersion < $version7) {
				$javaScriptFile = 'passwordreminder.js';
			} else {
				$javaScriptFile = 'passwordreminder7.js';
			}
			$backendReference->addJavascriptFile(
				$GLOBALS['BACK_PATH'] . '../'
				. ExtensionManagementUtility::siteRelPath('be_secure_pw')
				. 'Resources/Public/JavaScript/' . $javaScriptFile
			);
		}
	}

	/**
	 * looks for a password change and sets the field "tx_besecurepw_lastpwchange" with an actual timestamp
	 *
	 * @param $incomingFieldArray
	 * @param $table
	 * @param $id
	 * @param $parentObj
	 */
	public function processDatamap_preProcessFieldArray(&$incomingFieldArray, $table, $id, &$parentObj) {
		if ($table == 'be_users' && $incomingFieldArray['password'] != '') {
			$incomingFieldArray['tx_besecurepw_lastpwchange'] = time() + date('Z');
		}
	}
}

?>