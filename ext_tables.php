<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

/* override language file */
$TCA_DESCR['_MOD_user_setup']['refs'][] = 'EXT:be_secure_pw/Resources/Private/Language/ux_locallang_csh_mod.xml';