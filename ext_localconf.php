<?php
if (!defined ("TYPO3_MODE")) die ("Access denied.");

// here we register "tx_besecurepw_secure"
// for editing per tca form
$TYPO3_CONF_VARS['SC_OPTIONS']['tce']['formevals']['tx_besecurepw_secure'] = 'EXT:be_secure_pw/lib/class.tx_besecurepw_secure.php';

// for editing per "user settings"
if (t3lib_div::int_from_ver(TYPO3_version) >= 4002000 and t3lib_div::int_from_ver(TYPO3_version) < 4003000) {
   $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/setup/mod/index.php'] = PATH_typo3conf.'ext/be_secure_pw/v4.2/class.ux_SC_mod_user_setup_index.php';
} elseif (t3lib_div::int_from_ver(TYPO3_version) >= 4003000 and t3lib_div::int_from_ver(TYPO3_version) < 4004000) {
   $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/setup/mod/index.php'] = PATH_typo3conf.'ext/be_secure_pw/v4.3/class.ux_SC_mod_user_setup_index.php';
} elseif (t3lib_div::int_from_ver(TYPO3_version) >= 4004000) {
   $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/setup/mod/index.php'] = PATH_typo3conf.'ext/be_secure_pw/v4.4/class.ux_SC_mod_user_setup_index.php';
}

?>
