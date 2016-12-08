<?php
namespace SpoonerWeb\BeSecurePw\Evaluation;

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

use TYPO3\CMS\Core\Utility;

/**
 * Class PasswordEvaluator
 *
 * @package be_secure_pw
 * @author Thomas Loeffler <loeffler@spooner-web.de>
 */
class PasswordEvaluator
{

    /**
     * This function just return the field value as it is. No transforming,
     * hashing will be done on server-side.
     *
     * @return string JavaScript code for evaluation
     */
    public function returnFieldJS()
    {
        return 'return value;';
    }

    /**
     * Function uses Portable PHP Hashing Framework to create a proper password string if needed
     *
     * @param mixed $value The value that has to be checked.
     * @param string $is_in Is-In String
     * @param integer $set Determines if the field can be set (value correct) or not
     * @param boolean $storeFlashMessageInSession Used only for phpunit issues
     * @return string The new value of the field
     */
    public function evaluateFieldValue($value, $is_in, &$set, $storeFlashMessageInSession = true)
    {
        // if $value is a md5 hash, return the value directly
        if ($this->isMd5($value) || $this->isSalted($value)) {
            return $value;
        }

        $confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['be_secure_pw']);

        /** @var \TYPO3\CMS\Core\DataHandling\DataHandler $tce */
        $tce = Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\DataHandling\\DataHandler');
        $tce->BE_USER = $GLOBALS['BE_USER'];

        /** @var $logger \TYPO3\CMS\Core\Log\Logger */
        $logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Log\LogManager::class)
                    ->getLogger(__CLASS__);

        // get the languages from ext
        /** @var \TYPO3\CMS\Lang\LanguageService $languageService */
        $languageService = Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Lang\\LanguageService');
        $languageService->init($tce->BE_USER->uc['lang']);
        $languageService->includeLLFile('EXT:be_secure_pw/Resources/Private/Language/locallang.xml');
        /** @var \TYPO3\CMS\Core\Messaging\FlashMessageQueue $flashMessageQueue */
        $flashMessageQueue = Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Messaging\FlashMessageQueue::class,
            'core.template.flashMessages'
        );
        $set = true;

        $messages = [];
        // check for password length
        $passwordLength = (int)$confArr['passwordLength'];
        if ($confArr['passwordLength'] && $passwordLength) {
            if (strlen($value) < $confArr['passwordLength']) {
                /* password too short */
                $set = false;
                $logger->error(
                    sprintf($languageService->getLL('shortPassword'), $passwordLength)
                );
                $messages[] = sprintf($languageService->getLL('shortPassword'), $passwordLength);
            }
        }

        $counter = 0;
        $notUsed = array();

        // check for lowercase characters
        if ($confArr['lowercaseChar']) {
            if (preg_match("/[a-z]/", $value) > 0) {
                $counter++;
            } else {
                $notUsed[] = $languageService->getLL('lowercaseChar');
            }
        }

        // check for capital characters
        if ($confArr['capitalChar']) {
            if (preg_match("/[A-Z]/", $value) > 0) {
                $counter++;
            } else {
                $notUsed[] = $languageService->getLL('capitalChar');
            }
        }

        // check for digits
        if ($confArr['digit']) {
            if (preg_match("/[0-9]/", $value) > 0) {
                $counter++;
            } else {
                $notUsed[] = $languageService->getLL('digit');
            }
        }

        // check for special characters
        if ($confArr['specialChar']) {
            if (preg_match("/[^0-9a-z]/i", $value) > 0) {
                $counter++;
            } else {
                $notUsed[] = $languageService->getLL('specialChar');
            }
        }

        if ($counter < $confArr['patterns']) {
            /* password does not fit all conventions */
            $ignoredPatterns = $confArr['patterns'] - $counter;

            $additional = '';
            $set = false;

            if (is_array($notUsed) && sizeof($notUsed) > 0) {
                if (sizeof($notUsed) > 1) {
                    $additional = sprintf($languageService->getLL('notUsedConventions'), implode(', ', $notUsed));
                } else {
                    $additional = sprintf($languageService->getLL('notUsedConvention'), $notUsed[0]);
                }
            }

            if ($ignoredPatterns >= 1) {
                $label = $ignoredPatterns > 1 ? 'passwordConventions' : 'passwordConvention';
                $logger->error(
                    sprintf($languageService->getLL($label) . $additional, $ignoredPatterns)
                );
                $messages[] = sprintf($languageService->getLL($label) . $additional, $ignoredPatterns);
            }
        }

        /* no problems */
        if ($set) {
            // If no saltedpasswords are enabled, hash the password to prevent a clean password in DB
            if (!\TYPO3\CMS\Saltedpasswords\Utility\SaltedPasswordsUtility::isUsageEnabled('BE')) {
                $value = md5($value);
            }
            return $value;
        }

        $flashMessageQueue->addMessage(
            new \TYPO3\CMS\Core\Messaging\FlashMessage(
                implode(LF, $messages),
                $languageService->getLL('messageTitle'),
                \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR,
                $storeFlashMessageInSession
            )
        );

        // if password not valid return empty password
        return '';
    }

    /**
     * @param $password
     * @return boolean
     */
    protected function isMd5($password)
    {
        return (boolean)preg_match('/[0-9abcdef]{32,32}/', $password);
    }

    /**
     * @param $password
     * @return boolean
     */
    protected function isSalted($password)
    {
        if (!\TYPO3\CMS\Saltedpasswords\Utility\SaltedPasswordsUtility::isUsageEnabled('BE')) {
            return false;
        }

        $saltFactory = \TYPO3\CMS\Saltedpasswords\Salt\SaltFactory::getSaltingInstance($password, 'BE');
        if (!$saltFactory) {
            return false;
        }

        return $saltFactory->isValidSaltedPW($password);
    }
}
