<?php 

require_once(PATH_t3lib.'class.t3lib_tcemain.php');


class tx_besecurepw_secure {


	function returnFieldJS() {
		$js = "
			return value;
		";
		return $js;
	}
	
	function evaluateFieldValue($value, $is_in, $set, $onlyCheck=FALSE) {
		$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['be_secure_pw']);
		$exit = FALSE;
		$noMD5 = FALSE;
		
		if (t3lib_extMgm::isLoaded('t3sec_saltedpw')) {
			if (tx_t3secsaltedpw_div::isUsageEnabled('BE')) {
				$noMD5 = TRUE;
			}
		}
		
		if (t3lib_extMgm::isLoaded('saltedpasswords')) { 
			if (tx_saltedpasswords_div::isUsageEnabled('BE')) {
				$noMD5 = TRUE;
			}
		}
		
		if ($value and strlen($value) == 32) {
			return $value;
		}
		
		// check for password length
		if ($confArr['passwordLength'] and $passwordLength = intval($confArr['passwordLength'])) {
			if (strlen($value) < $confArr['passwordLength']) {
				$exit = TRUE;	
			}
		}
		
		if (!$exit) {
			$counter = 0;
			$notUsed = array();
			
			// check for lowercase characters
			if ($confArr['lowercaseChar']) {
				if (preg_match("/[a-z]/", $value) > 0) {
					$counter++;
				} else {
					$notUsed[] = 'lowercase character';
				}
			}

			// check for capital characters
			if ($confArr['capitalChar']) {
				if (preg_match("/[A-Z]/", $value) > 0) {
					$counter++;
				} else {
					$notUsed[] = 'capital character';
				}
			}
			
		 	// check for digits
			if ($confArr['digit']) {
				if (preg_match("/[0-9]/", $value) > 0) {
					$counter++;
				} else {
					$notUsed[] = 'digit';
				}
			}

			// check for special characters
			if ($confArr['specialChar']) {
                if (preg_match("/[^0-9a-z]/i", $value) > 0) {
                    $counter++;
				} else {
					$notUsed[] = 'special character';
				}
			}
		}
		
		// create tce object for logging
		$tce = t3lib_div::makeInstance('t3lib_tcemain');
		$tce->BE_USER = $GLOBALS['BE_USER'];
        

		if ($exit) { // password too short
			$tce->log('be_users',0,5,0,1,"Attempt to modify password. The password is too short! Please use at least %s characters.", FALSE, array($passwordLength));
			if ($onlyCheck) {
				return array('errorMessage' => 'password_too_short', 'errorValue' => $passwordLength, 'notUsed' => array());
			}
		} elseif ($counter < $confArr['patterns']) { // password does not fit all conventions
			$ignoredPatterns = $confArr['patterns'] - $counter;
			
			$additional = '';
			if (is_array($notUsed) and sizeof($notUsed) > 0) {

				if (sizeof($notUsed) > 1) {
					$additional = ' Not used conventions are '.implode(', ', $notUsed).'!';
				} else {
					$additional = ' Not used convention is '.$notUsed[0].'!';
				}
			}
			
			if ($ignoredPatterns == '1') {
				$tce->log('be_users',0,5,0,1,"Attempt to modify password. %s password convention was ignored!".$additional, FALSE, array($ignoredPatterns));
				if ($onlyCheck) {
					return array('errorMessage' => 'password_no_convention', 'errorValue' => $ignoredPatterns, 'notUsed' => $notUsed);
				}
			} elseif ($ignoredPatterns > 1) {
				$tce->log('be_users',0,5,0,1,"Attempt to modify password. %s password conventions were ignored!".$additional, FALSE, array($ignoredPatterns));
				if ($onlyCheck) {
					return array('errorMessage' => 'password_no_conventions', 'errorValue' => $ignoredPatterns, 'notUsed' => $notUsed);
				}
			}
		} else { // no problems
			if ($onlyCheck or $noMD5) {
				return $value;
			} else {
				return md5($value);
			}
		}

		// if error return old password!
		return $GLOBALS['BE_USER']->user['password'];
	}

}

?>