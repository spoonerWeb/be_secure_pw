<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

/* set password evaluation for password field in be_users */
$TCA['be_users']['columns']['password']['config']['eval'] = 'required,SpoonerWeb\\BeSecurePw\\Evaluation\\PasswordEvaluator,tx_saltedpasswords_eval_be,password';

/* override language file */
$TCA_DESCR['_MOD_user_setup']['refs'][] = 'EXT:be_secure_pw/Resources/Private/Language/ux_locallang_csh_mod.xml';
?>