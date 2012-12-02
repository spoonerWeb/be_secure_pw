<?php 
if (!defined ("TYPO3_MODE")) die ("Access denied.");


$tempColumns = array(
	'tx_besecurepw_lastpwchange' => array(
		'exclude' => 0,
		'label' => 'LLL:EXT:be_secure_pw/res/lang/locallang.xml:be_users.tx_besecurepw_lastpwchange',
		'config' => array(
			'type' => 'input',
			'size' => 12,
			'eval' => 'datetime',
		)
	),
);

t3lib_div::loadTCA('be_users');
t3lib_extMgm::addTCAcolumns('be_users', $tempColumns, 1);
t3lib_extMgm::addToAllTCAtypes('be_users', 'tx_besecurepw_lastpwchange;;;;1-1-1');

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

/* override language file */
$TCA_DESCR['_MOD_user_setup']['refs'][] = 'EXT:be_secure_pw/res/lang/ux_locallang_csh_mod.xml';
?>