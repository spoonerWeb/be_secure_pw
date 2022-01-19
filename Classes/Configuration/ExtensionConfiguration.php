<?php
declare(strict_types = 1);

namespace SpoonerWeb\BeSecurePw\Configuration;

/*
 * This file is part of a TYPO3 extension.
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

/**
 * Class ExtensionConfiguration
 *
 * @author Thomas Löffler <loeffler@spooner-web.de>
 */
class ExtensionConfiguration
{
    public static function getExtensionConfig(): ?array
    {
        return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class)
            ->get('be_secure_pw');
    }
}
