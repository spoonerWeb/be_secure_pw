<?php

########################################################################
# Extension Manager/Repository config file for ext "be_secure_pw".
#
# Auto generated 27-07-2010 13:32
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
	'version' => '1.0.1',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'beta',
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
	'_md5_values_when_last_written' => 'a:15:{s:9:"ChangeLog";s:4:"99e6";s:10:"README.txt";s:4:"ee2d";s:30:"class.tx_besecurepw_secure.php";s:4:"6dd9";s:21:"ext_conf_template.txt";s:4:"a36c";s:12:"ext_icon.gif";s:4:"70ec";s:17:"ext_localconf.php";s:4:"2911";s:17:"ext_locallang.xml";s:4:"5eb0";s:14:"ext_tables.php";s:4:"afad";s:14:"doc/manual.sxw";s:4:"03cb";s:19:"doc/wizard_form.dat";s:4:"d310";s:20:"doc/wizard_form.html";s:4:"39e1";s:24:"res/js/passwordtester.js";s:4:"342a";s:41:"v4.2/class.ux_SC_mod_user_setup_index.php";s:4:"64ae";s:41:"v4.3/class.ux_SC_mod_user_setup_index.php";s:4:"5768";s:41:"v4.4/class.ux_SC_mod_user_setup_index.php";s:4:"7afa";}',
	'suggests' => array(
	),
);

?>