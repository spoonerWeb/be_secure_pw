<?php
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

class user_usersetup_hook {

	/**
	 * Add flash message with instructions for user.
	 *
	 * @param &$params
	 * @param &$parentObj
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
					$GLOBALS['LANG'] = t3lib_div::makeInstance('language');
					$GLOBALS['LANG']->init($GLOBALS['BE_USER']->uc['lang']);
				}
				$GLOBALS['LANG']->includeLLFile('EXT:be_secure_pw/res/lang/locallang.xml');
					// how many parameters have to be checked
				$toCheckParams = array('lowercaseChar', 'capitalChar', 'digit', 'specialChar');
				$checkParameter = array();
				foreach ($toCheckParams as $parameter) {
					if ($extConf[$parameter] == 1) {
						$checkParameter[] = $GLOBALS['LANG']->getLL($parameter);
					}
				}

					// flash message with instructions for the user
				$flashMessage = t3lib_div::makeInstance(
					't3lib_FlashMessage',
					sprintf($GLOBALS['LANG']->getLL('beSecurePw.description'), $extConf['passwordLength'], implode(', ', $checkParameter), $extConf['patterns']),
					$GLOBALS['LANG']->getLL('beSecurePw.header'),
					t3lib_FlashMessage::INFO,
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
		 * Hook-function: inject additional JS code and a flash message
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
			$parentObj->JScodeArray['be_secure_pw_inline'] = 'var beSecurePwConf = '.json_encode($extConf);

				// add JS code for password validation
			$parentObj->JScode .= '<script type="text/javascript" src="/'.t3lib_extMgm::siteRelPath('be_secure_pw').'res/js/passwordtester.js"></script>';

		}
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/be_secure_pw/classes/class.user_usersetup_hook.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/be_secure_pw/classes/class.user_usersetup_hook.php']);
}

?>