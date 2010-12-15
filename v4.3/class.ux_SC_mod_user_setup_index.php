<?php 

require_once(PATH_typo3conf.'ext/be_secure_pw/lib/class.tx_besecurepw_secure.php'); /* CHANGED: add evaluation class */

class ux_SC_mod_user_setup_index extends SC_mod_user_setup_index {
	
	/******************************
	 *
	 * Saving data
	 *
	 ******************************/

	/**
	 * If settings are submitted to _POST[DATA], store them
	 * NOTICE: This method is called before the template.php is included. See buttom of document
	 *
	 * @return	void
	 */
	function storeIncomingData()	{
		global $BE_USER;

			// First check if something is submittet in the data-array from POST vars
		$d = t3lib_div::_POST('data');
		$columns = $GLOBALS['TYPO3_USER_SETTINGS']['columns'];
		$beUserId = $BE_USER->user['uid'];
		$storeRec = array();

		if (is_array($d))	{

				// UC hashed before applying changes
			$save_before = md5(serialize($BE_USER->uc));

				// PUT SETTINGS into the ->uc array:

				// reload left frame when switching BE language
			if (isset($d['lang']) && ($d['lang'] != $BE_USER->uc['lang'])) {
				$this->languageUpdate = true;
			}

			if ($d['setValuesToDefault']) {
					// If every value should be default
				$BE_USER->resetUC();
			} elseif ($d['clearSessionVars']) {
				foreach ($BE_USER->uc as $key => $value) {
					if (!isset($columns[$key])) {
						unset ($BE_USER->uc[$key]);
					}
				}
			} else {
					// save all submitted values if they are no array (arrays are with table=be_users) and exists in $GLOBALS['TYPO3_USER_SETTINGS'][columns]
				foreach($columns as $field => $config) {
					if ($config['table']) {
						if ($config['table'] == 'be_users' && !in_array($field, array('password', 'password2', 'email', 'realName', 'admin'))) {
							if (!isset($config['access']) || $this->checkAccess($config) && $BE_USER->user[$field] !== $d['be_users'][$field]) {
								$storeRec['be_users'][$beUserId][$field] = $d['be_users'][$field];
								$BE_USER->user[$field] = $d['be_users'][$field];
							}
						}
					}
					if ($config['type'] == 'check') {
						$BE_USER->uc[$field] = isset($d[$field]) ? 1 : 0;
					} else {
						$BE_USER->uc[$field] = htmlspecialchars($d[$field]);
					}
				}
			}
			$BE_USER->overrideUC();	// Inserts the overriding values.

			$save_after = md5(serialize($BE_USER->uc));
			if ($save_before!=$save_after)	{	// If something in the uc-array of the user has changed, we save the array...
				$BE_USER->writeUC($BE_USER->uc);
				$BE_USER->writelog(254, 1, 0, 1, 'Personal settings changed', array());
				$this->setupIsUpdated = TRUE;
			}

				// Personal data for the users be_user-record (email, name, password...)
				// If email and name is changed, set it in the users record:
			$be_user_data = $d['be_users'];

			$this->passwordIsSubmitted = (strlen($be_user_data['password']) > 0);
			/* CHANGED 
			$passwordIsConfirmed = ($this->passwordIsSubmitted && $be_user_data['password'] === $be_user_data['password2']);*/
			
			/* TO */
			$passwordChecker = new tx_besecurepw_secure();
			$passwordIsConfirmed = ($this->passwordIsSubmitted && $be_user_data['password'] === $be_user_data['password2'] && $checkedPassword = $passwordChecker->evaluateFieldValue($be_user_data['password2'], 1, 1, 1));
			/* END */
			
				// Update the real name:
			if ($be_user_data['realName'] !== $BE_USER->user['realName']) {
				$BE_USER->user['realName'] = $storeRec['be_users'][$beUserId]['realName'] = substr($be_user_data['realName'], 0, 80);
			}
				// Update the email address:
			if ($be_user_data['email'] !== $BE_USER->user['email']) {
				$BE_USER->user['email'] = $storeRec['be_users'][$beUserId]['email'] = substr($be_user_data['email'], 0, 80);
			}
				// Update the password:
			if ($passwordIsConfirmed) {
				$storeRec['be_users'][$beUserId]['password'] = $be_user_data['password2'];
				/* CHANGED
				$this->passwordIsUpdated = TRUE;*/
				
				/* TO */
				if (is_array($checkedPassword)) {
					$this->passwordIsUpdated = $checkedPassword;
				} else {
					$this->passwordIsUpdated = TRUE;
				}
				/* END */
			}

				// Persist data if something has changed:
			if (count($storeRec)) {
					// Make instance of TCE for storing the changes.
				$tce = t3lib_div::makeInstance('t3lib_TCEmain');
				$tce->stripslashes_values=0;
				$tce->start($storeRec,Array(),$BE_USER);
				$tce->admin = 1;	// This is so the user can actually update his user record.
				$tce->bypassWorkspaceRestrictions = TRUE;	// This is to make sure that the users record can be updated even if in another workspace. This is tolerated.
				$tce->process_datamap();
				unset($tce);

				if (!$this->passwordIsUpdated || count($storeRec['be_users'][$beUserId]) > 1) {
					$this->setupIsUpdated = TRUE;
				}
			}
		}
	}
	
	/**
	 * Generate the main settings formular:
	 *
	 * @return	void
	 */
	function main()	{
		global $BE_USER,$LANG,$BACK_PATH,$TBE_MODULES;
		
		
		$LANG->includeLLFile('EXT:be_secure_pw/ext_locallang.xml'); /* CHANGED: add locallang file */

			// file creation / delete
		if ($this->isAdmin) {
			if (t3lib_div::_POST('deleteInstallToolEnableFile')) {
				unlink(PATH_typo3conf . 'ENABLE_INSTALL_TOOL');
			}
			if (t3lib_div::_POST('createInstallToolEnableFile')) {
				touch(PATH_typo3conf . 'ENABLE_INSTALL_TOOL');
			}
		}

		if ($this->languageUpdate) {
			$this->doc->JScodeArray['languageUpdate'] .=  '
				if (top.refreshMenu) {
					top.refreshMenu();
				} else {
					top.TYPO3ModuleMenu.refreshMenu();
				}

				if (top.shortcutFrame) {
					top.shortcutFrame.refreshShortcuts();
				}
			';
		}

			// Start page:
		$this->doc->loadJavascriptLib('md5.js');

			// use a wrapper div
		$this->content .= '<div id="user-setup-wrapper">';

			// Load available backend modules
		$this->loadModules = t3lib_div::makeInstance('t3lib_loadModules');
		$this->loadModules->observeWorkspaces = true;
		$this->loadModules->load($TBE_MODULES);

		$this->content .= $this->doc->header($LANG->getLL('UserSettings').' - '.$BE_USER->user['realName'].' ['.$BE_USER->user['username'].']');

			// show if setup was saved
		if ($this->setupIsUpdated) {
			$flashMessage = t3lib_div::makeInstance(
				't3lib_FlashMessage',
				$LANG->getLL('setupWasUpdated'),
				$LANG->getLL('UserSettings')
			);
			$this->content .= $flashMessage->render();
		}
			// If password is updated, output whether it failed or was OK.
		if ($this->passwordIsSubmitted) {
			if ($this->passwordIsUpdated) {
				/* CHANGED
				$flashMessage = t3lib_div::makeInstance(
						't3lib_FlashMessage',
						$LANG->getLL('newPassword_ok'),
						$LANG->getLL('newPassword')
				);*/
				/* TO */
				if (is_array($this->passwordIsUpdated)) {
					$flashMessage = t3lib_div::makeInstance(
						't3lib_FlashMessage',
						sprintf($LANG->getLL($this->passwordIsUpdated['errorMessage']), $this->passwordIsUpdated['errorValue']).(sizeof($this->passwordIsUpdated['notUsed']) > 0 ? ((sizeof($this->passwordIsUpdated['notUsed']) > 1) ? ($LANG->getLL('additional_text_multi').implode(', ', $this->passwordIsUpdated['notUsed'])) : ($LANG->getLL('additional_text_single').$this->passwordIsUpdated['notUsed'][0])) .'!': ''),
						$LANG->getLL('newPassword'),
						t3lib_FlashMessage::ERROR
					);
				} else {
					$flashMessage = t3lib_div::makeInstance(
						't3lib_FlashMessage',
						$LANG->getLL('newPassword_ok'),
						$LANG->getLL('newPassword')
					);
				}
				/* END */
			} else {
				$flashMessage = t3lib_div::makeInstance(
					't3lib_FlashMessage',
					$LANG->getLL('newPassword_failed'),
					$LANG->getLL('newPassword'),
					t3lib_FlashMessage::ERROR
				);
			}
			$this->content .= $flashMessage->render();
		}


			// render the menu items
		$menuItems = $this->renderUserSetup();

		$this->content .= $this->doc->spacer(20) . $this->doc->getDynTabMenu($menuItems, 'user-setup', false, false, 100, 1, false, 1, $this->dividers2tabs);


			// Submit and reset buttons
		$this->content .= $this->doc->spacer(20);
		$this->content .= $this->doc->section('',
			t3lib_BEfunc::cshItem('_MOD_user_setup', 'reset', $BACK_PATH) . '
			<input type="hidden" name="simUser" value="'.$this->simUser.'" />
			<input type="submit" name="submit" value="'.$LANG->getLL('save').'" />
			<input type="submit" name="data[setValuesToDefault]" value="'.$LANG->getLL('resetConfiguration').'" onclick="return confirm(\''.$LANG->getLL('setToStandardQuestion').'\');" />
			<input type="submit" name="data[clearSessionVars]" value="'.$LANG->getLL('clearSessionVars').'" />'
		);



			// Notice
		$this->content .= $this->doc->spacer(30);
		$this->content .= $this->doc->section('', $LANG->getLL('activateChanges'));

			// Setting up the buttons and markers for docheader
		$docHeaderButtons = $this->getButtons();
		$markers['CSH'] = $docHeaderButtons['csh'];
		$markers['CONTENT'] = $this->content;

			// Build the <body> for the module
		$this->content = $this->doc->startPage($LANG->getLL('UserSettings'));
		$this->content.= $this->doc->moduleBody($this->pageinfo, $docHeaderButtons, $markers);
			// end of wrapper div
		$this->content .= '</div>';
		$this->content.= $this->doc->endPage();
		$this->content = $this->doc->insertStylesAndJS($this->content);

	}


	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/be_secure_pw/v4.3/class.ux_SC_mod_user_setup_index.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/be_secure_pw/v4.3/class.ux_SC_mod_user_setup_index.php']);
}

?>