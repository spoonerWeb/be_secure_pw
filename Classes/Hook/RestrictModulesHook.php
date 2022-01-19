<?php

declare(strict_types=1);

namespace SpoonerWeb\BeSecurePw\Hook;

/**
 * This file is part of the be_secure_pw project.
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
use TYPO3\CMS\Core\Authentication\AbstractUserAuthentication;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Class BackendHook
 *
 * @author Andreas Kießling <andreas.kiessling@web.de>
 * @author Christian Plattner <Christian.Plattner@world-direct.at>
 */
class RestrictModulesHook implements SingletonInterface
{
    /**
     * Insert JavaScript code to refresh the module menu, if the password was updated and
     * the "force" option was set. The menu then only shows a limited set of available backend modules.
     *
     * PageRenderer::executePostRenderHook
     *
     * @param array $params
     * @param \TYPO3\CMS\Core\Page\PageRenderer $pageRenderer
     */
    public function addRefreshJavaScript(array $params, PageRenderer $pageRenderer)
    {
        if (BackendHook::$insertModuleRefreshJS) {
            $params['jsFooterLibs'] .= '<script>top.location.reload();</script>';
        }
    }

    /**
     * If the password is expired, only load the necessary modules to change the password
     *
     * @param array $params
     * @param \TYPO3\CMS\Core\Authentication\AbstractUserAuthentication $pObj
     */
    public function postUserLookUp(array $params, AbstractUserAuthentication $pObj)
    {
        if (PasswordExpirationUtility::isBeUserPasswordExpired()) {
            // remove admin rights, because otherwise we can't restrict access to the modules
            $GLOBALS['BE_USER']->user['admin'] = 0;
            // this grants the user access to the modules
            $GLOBALS['BE_USER']->user['userMods'] = 'user,user_setup';
            // remove all groups from the user, so he can not get access to any other modules
            // than the ones we granted him
            $GLOBALS['BE_USER']->user['usergroup'] = '';
            // allow access to live and workspace, if the user is currently in a workspace,
            // but the access is removed due to missing usergroup
            $GLOBALS['BE_USER']->user['workspace_perms'] = 3;
        }
    }
}
