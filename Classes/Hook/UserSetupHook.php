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

use TYPO3\CMS\Core\Utility;
use TYPO3\CMS\Core\Messaging\FlashMessage;

/**
 * Class UserSetupHook
 *
 * @package be_secure_pw
 * @author Thomas Loeffler <loeffler@spooner-web.de>
 */
class UserSetupHook {

	/**
	 * Add flash message with instructions for user.
	 *
	 * @param array &$params
	 * @param \TYPO3\CMS\Backend\Template\DocumentTemplate &$parentObj
	 */
	public function moduleBodyPostProcess(&$params, &$parentObj) {
		// execute only in user setup module
		if ($parentObj->scriptID == 'ext/setup/mod/index.php') {
			// don't override existing flash messages
			if (!array_key_exists('FLASHMESSAGES', $params['markers'])) {
				// get configuration of a secure password
				$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['be_secure_pw']);

				// get the languages from ext
				if (empty($GLOBALS['LANG'])) {
					$GLOBALS['LANG'] = Utility\GeneralUtility::makeInstance('language');
					$GLOBALS['LANG']->init($GLOBALS['BE_USER']->uc['lang']);
				}
				$GLOBALS['LANG']->includeLLFile('EXT:be_secure_pw/Resources/Private/Language/locallang.xml');
				// how many parameters have to be checked
				$toCheckParams = array(
					'lowercaseChar',
					'capitalChar',
					'digit',
					'specialChar'
				);
				$checkParameter = array();
				foreach ($toCheckParams as $parameter) {
					if ($extConf[$parameter] == 1) {
						$checkParameter[] = $GLOBALS['LANG']->getLL($parameter);
					}
				}

				// flash message with instructions for the user
				$flashMessage = Utility\GeneralUtility::makeInstance(
					'TYPO3\CMS\Core\Messaging\FlashMessage',
					sprintf(
						$GLOBALS['LANG']->getLL('beSecurePw.description'),
						$extConf['passwordLength'],
						implode(', ', $checkParameter),
						$extConf['patterns']
					),
					$GLOBALS['LANG']->getLL('beSecurePw.header'),
					FlashMessage::INFO,
					TRUE
				);
				$params['markers']['FLASHMESSAGES'] = '<div id="typo3-messages">' . $flashMessage->render() . '</div>';

				// put flash message in front of content
				if (strpos($params['moduleBody'], '###FLASHMESSAGES###') === FALSE) {
					$params['moduleBody'] = str_replace(
						'###CONTENT###',
						'###FLASHMESSAGES######CONTENT###',
						$params['moduleBody']
					);
				}
			}
		}
	}

	/**
	 * Hook-function: inject additional JS code
	 * called in typo3/template.php:template->startPage
	 *
	 * @param  $params
	 * @param  $parentObj
	 */
	public function preStartPageHook($params, &$parentObj) {
		if ($parentObj->scriptID == 'ext/setup/mod/index.php') { // execute only in user setup module

			// get configuration of a secure password
			$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['be_secure_pw']);

			// add configuration for JS function in json format
			$parentObj->JScodeArray['be_secure_pw_inline'] = 'var beSecurePwConf = ' . json_encode($extConf);

			// add JS code for password validation
			$parentObj->JScode .= '<script type="text/javascript" src="'
				. $GLOBALS['BACK_PATH'] . '../'
				. Utility\ExtensionManagementUtility::siteRelPath('be_secure_pw')
				. 'Resources/Public/JavaScript/passwordtester.js"></script>';

		}
	}

	/**
	 * @param array $params
	 * @param \TYPO3\CMS\Setup\Controller\SetupModuleController $parentObject
	 * @return string
	 */
	public function setupScriptHook($params, &$parentObject) {
		// add JS code for password validation
		$parentObject->doc->JScode .= '<script type="text/javascript" src="'
			. $GLOBALS['BACK_PATH'] . '../'
			. Utility\ExtensionManagementUtility::siteRelPath('be_secure_pw')
			. 'Resources/Public/JavaScript/passwordtester7.js"></script>';

		// get configuration of a secure password
		$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['be_secure_pw']);

		return '<script type="text/javascript">var beSecurePwConf = ' . json_encode($extConf) . '</script>';
	}

}
?>