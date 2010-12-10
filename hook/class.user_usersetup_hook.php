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
	 * Hook-function: inject additional HTML code
	 * called in index.php:SC_mod_user_setup_index->main
	 *
	 * @param array $params
	 * @param SC_mod_user_setup_index $parentObj
	 */
	public function initSettingsForm($params, &$parentObj) {
        $extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['be_secure_pw']);
        $parentObj->doc->JScodeArray['beSecurePwConf'] = 'var beSecurePwConf = '.json_encode($extConf);

        #t3lib_div::debug($parentObj->doc->endOfPageJsBlock);
        $parentObj->doc->JScode .= '<script type="text/javascript" src="'.$parentObj->doc->backPath.'../typo3conf/ext/be_secure_pw/res/js/passwordtester.js"></script>';
    }

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/be_secure_pw/classes/class.user_usersetup_hook.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/be_secure_pw/classes/class.user_usersetup_hook.php']);
}

?>