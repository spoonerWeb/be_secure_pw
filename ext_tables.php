<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

$tempColumns = array(
	'tx_besecurepw_lastpwchange' => array(
		'exclude' => 0,
		'label' => 'LLL:EXT:be_secure_pw/Resources/Private/Language/locallang.xml:be_users.tx_besecurepw_lastpwchange',
		'config' => array(
			'type' => 'input',
			'size' => 12,
			'eval' => 'datetime',
		)
	),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('be_users', $tempColumns, 1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('be_users', 'tx_besecurepw_lastpwchange;;;;1-1-1');

$TCA['be_users']['columns']['password']['config']['eval'] = 'trim,required,PasswordEvaluator,password';
if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('saltedpasswords')) {
	if (\TYPO3\CMS\Saltedpasswords\Utility\SaltedPasswordsUtility::isUsageEnabled('BE')) {
		$TCA['be_users']['columns']['password']['config']['eval'] =
			'trim,required,PasswordEvaluator,tx_saltedpasswords_eval_be,password';
	}
}

/* override language file */
$TCA_DESCR['_MOD_user_setup']['refs'][] = 'EXT:be_secure_pw/Resources/Private/Language/ux_locallang_csh_mod.xml';
?>