<?php

########################################################################
# Extension Manager/Repository config file for ext "be_secure_pw".
#
# Auto generated 18-01-2012 11:04
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Make BE user password really secure',
	'description' => 'You can set password conventions to force secure passwords for BE users. Works with rsa auth and salted passwords!',
	'category' => 'be',
	'shy' => 0,
	'version' => '2.2.1',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Thomas Loeffler',
	'author_email' => 'typo3@tomalo.de',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'typo3' => '4.2.7-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:20:{s:9:"ChangeLog";s:4:"4594";s:10:"README.txt";s:4:"ee2d";s:21:"ext_conf_template.txt";s:4:"dea1";s:12:"ext_icon.gif";s:4:"70ec";s:17:"ext_localconf.php";s:4:"3d9d";s:17:"ext_locallang.xml";s:4:"5092";s:14:"ext_tables.php";s:4:"3df3";s:14:"doc/manual.sxw";s:4:"03cb";s:19:"doc/wizard_form.dat";s:4:"d310";s:20:"doc/wizard_form.html";s:4:"39e1";s:34:"hook/class.user_usersetup_hook.php";s:4:"0287";s:34:"lib/class.tx_besecurepw_secure.php";s:4:"9207";s:18:"res/img/accept.png";s:4:"8bfe";s:18:"res/img/cancel.png";s:4:"757a";s:24:"res/js/passwordtester.js";s:4:"ebfa";s:22:"res/lang/locallang.xml";s:4:"30ba";s:33:"res/lang/ux_locallang_csh_mod.xml";s:4:"c02a";s:41:"v4.2/class.ux_SC_mod_user_setup_index.php";s:4:"b712";s:41:"v4.3/class.ux_SC_mod_user_setup_index.php";s:4:"b7d5";s:41:"v4.4/class.ux_SC_mod_user_setup_index.php";s:4:"c1f6";}',
	'suggests' => array(
	),
);

?>