<?php
namespace SpoonerWeb\BeSecurePw\Hook;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
use SpoonerWeb\BeSecurePw\Utilities\PasswordExpirationUtility;

/**
 * Class BackendHook
 *
 * @package be_secure_pw
 * @author Andreas Kießling <andreas.kiessling@web.de>
 * @author Christian Plattner <Christian.Plattner@world-direct.at>
 */
class RestrictModulesHook implements \TYPO3\CMS\Core\SingletonInterface
{
    /**
     * Insert JavaScript code to refresh the module menu, if the password was updated and
     * the "force" option was set. The menu then only shows a limited set of available backend modules.
     *
     * @param array $params
     * @param mixed $pObj Reference back to the calling object (called from two different hooks, but we do not need it anyway)
     * @return string
     */
    public function addRefreshJavaScript(array $params, $pObj)
    {exit('...');
        if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['be_secure_pw']['insertModuleRefreshJS']) {
            $pageRenderer = $GLOBALS['TBE_TEMPLATE']->getPageRenderer();
            $label = $GLOBALS['LANG']->sL('LLL:EXT:be_secure_pw/Resources/Private/Language/locallang.xml:beSecurePw.backendNeedsToReload');
            $pageRenderer->addExtOnReadyCode(
                'alert("' . $label . '");
					top.location.reload();
					'
            );
        }
    }

    /**
     * If the password is expired, only load the necessary modules to change the password
     *
     * @param array $params
     * @param mixed $pObj
     */
    public function postUserLookUp(array $params, $pObj)
    {
        if (PasswordExpirationUtility::isBeUserPasswordExpired()) {
            // remove admin rights, because otherwise we can't restrict access to the modules
            $GLOBALS['BE_USER']->user['admin'] = 0;
            // this grants the user access to the modules
            $GLOBALS['BE_USER']->user['userMods'] = 'user,user_setup';
            // remove all groups from the user, so he can not get access to any other modules than the ones we granted him
            $GLOBALS['BE_USER']->user['usergroup'] = '';
            // allow access to live and workspace, if the user is currently in a workspace, but the access is removed due to missing usergroup
            $GLOBALS['BE_USER']->user['workspace_perms'] = 3;

            // Disable all columns except password
            if (!\TYPO3\CMS\Core\Utility\GeneralUtility::compat_version(7.6)) {
                $GLOBALS['TYPO3_USER_SETTINGS']['columns'] = array(
                    'password' => $GLOBALS['TYPO3_USER_SETTINGS']['columns']['password'],
                    'password2' => $GLOBALS['TYPO3_USER_SETTINGS']['columns']['password2'],
                );

                // Override showitem to remove tabs and all fields except password
                $GLOBALS['TYPO3_USER_SETTINGS']['showitem'] = '--div--;LLL:EXT:be_secure_pw/Resources/Private/Language/ux_locallang_csh_mod.xml:option_newPassword.description,password,password2';
            } else {
                $GLOBALS['TYPO3_USER_SETTINGS']['columns'] = array(
                    'passwordCurrent' => $GLOBALS['TYPO3_USER_SETTINGS']['columns']['passwordCurrent'],
                    'password' => $GLOBALS['TYPO3_USER_SETTINGS']['columns']['password'],
                    'password2' => $GLOBALS['TYPO3_USER_SETTINGS']['columns']['password2'],
                );

                // Override showitem to remove tabs and all fields except password
                $GLOBALS['TYPO3_USER_SETTINGS']['showitem'] = '--div--;LLL:EXT:be_secure_pw/Resources/Private/Language/ux_locallang_csh_mod.xml:option_newPassword.description,passwordCurrent,password,password2';
            }
        }
    }
}
