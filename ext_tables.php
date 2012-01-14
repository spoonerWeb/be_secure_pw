<?php 
if (!defined ("TYPO3_MODE")) die ("Access denied.");

t3lib_div::loadTCA('be_users');
$TCA['be_users']['columns']['password']['config']['eval'] = 'trim,required,tx_besecurepw_secure,password'; 
if (t3lib_extMgm::isLoaded('t3sec_saltedpw')) { 
	if (tx_t3secsaltedpw_div::isUsageEnabled('BE')) {
		$TCA['be_users']['columns']['password']['config']['eval'] = 'trim,required,tx_besecurepw_secure,tx_t3secsaltedpw_salted_be,password'; #trim,required,tx_t3secsaltedpw_salted_be,password
	}
}

if (t3lib_extMgm::isLoaded('saltedpasswords')) { 
	if (tx_saltedpasswords_div::isUsageEnabled('BE')) {
		$TCA['be_users']['columns']['password']['config']['eval'] = 'trim,required,tx_besecurepw_secure,tx_saltedpasswords_eval_be,password'; #trim,required,tx_t3secsaltedpw_salted_be,password
	}
}

/* NO MD5 EVALUATION */
if (t3lib_div::int_from_ver(TYPO3_version) >= 4003000) {
	$TYPO3_USER_SETTINGS['columns']['password']['eval'] = '';
	$TYPO3_USER_SETTINGS['columns']['password2']['eval'] = '';
}

/* override language file */
if (t3lib_div::int_from_ver(TYPO3_version) >= 4005000) {
    $TCA_DESCR['_MOD_user_setup']['refs'][] = 'EXT:be_secure_pw/res/lang/ux_locallang_csh_mod.xml';
}
?>