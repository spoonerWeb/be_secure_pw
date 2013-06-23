<?php
	if (!defined("TYPO3_MODE")) die ("Access denied.");

	// here we register "tx_besecurepw_secure"
	// for editing per tca form
	$TYPO3_CONF_VARS['SC_OPTIONS']['tce']['formevals']['tx_besecurepw_secure'] = 'EXT:be_secure_pw/lib/class.tx_besecurepw_secure.php';

	// for editing per "user settings"
	$TYPO3_CONF_VARS['SC_OPTIONS']['typo3/template.php']['preStartPageHook'][] = 'EXT:be_secure_pw/hook/class.user_usersetup_hook.php:&user_usersetup_hook->preStartPageHook';
	$TYPO3_CONF_VARS['SC_OPTIONS']['typo3/template.php']['moduleBodyPostProcess'][] = 'EXT:be_secure_pw/hook/class.user_usersetup_hook.php:&user_usersetup_hook->moduleBodyPostProcess';

	// password reminder
	$TYPO3_CONF_VARS['SC_OPTIONS']['typo3/backend.php']['constructPostProcess'][] = 'EXT:be_secure_pw/hook/class.user_backend_hook.php:&user_backend_hook->constructPostProcess';
	$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['be_secure_pw'] = 'EXT:be_secure_pw/hook/class.user_backend_hook.php:&user_backend_hook';

?>
