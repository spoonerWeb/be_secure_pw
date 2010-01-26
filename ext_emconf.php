<?php

########################################################################
# Extension Manager/Repository config file for ext: "be_secure_pw"
#
# Auto generated 18-01-2010 08:57
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Make BE user password really secure',
	'description' => 'You can set password conventions to force secure passwords for BE users. Works with TYPO3 4.3.x and saltedpasswords or TYPO3 4.2.x. and t3sec_saltedpw.',
	'category' => 'be',
	'author' => 'Thomas Loeffler',
	'author_email' => 'typo3@tomalo.de',
	'shy' => '',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'beta',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => '',
	'version' => '0.2.0',
	'constraints' => array(
		'depends' => array(
			'typo3' => '4.2.7-4.3.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:13:{s:9:"ChangeLog";s:4:"65cd";s:10:"README.txt";s:4:"ee2d";s:30:"class.tx_besecurepw_secure.php";s:4:"255d";s:21:"ext_conf_template.txt";s:4:"a36c";s:12:"ext_icon.gif";s:4:"70ec";s:17:"ext_localconf.php";s:4:"8a15";s:17:"ext_locallang.xml";s:4:"5eb0";s:14:"ext_tables.php";s:4:"afad";s:14:"doc/manual.sxw";s:4:"03cb";s:19:"doc/wizard_form.dat";s:4:"d310";s:20:"doc/wizard_form.html";s:4:"39e1";s:41:"v4.2/class.ux_SC_mod_user_setup_index.php";s:4:"64ae";s:41:"v4.3/class.ux_SC_mod_user_setup_index.php";s:4:"4385";}',
	'suggests' => array(
	),
);

?>