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
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Lang\LanguageService;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Crypto\PasswordHashing\SaltedPasswordsUtility;
// use TYPO3\CMS\Saltedpasswords\Salt\SaltFactory;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;

/**
 * Class PasswordEvaluator
 *
 * @author Thomas Loeffler <loeffler@spooner-web.de>
 */
class PasswordEvaluator
{
    const PATTERN_LOWER_CHAR = '/[a-z]/';
    const PATTERN_CAPITAL_CHAR = '/[A-Z]/';
    const PATTERN_DIGIT = '/[0-9]/';
    const PATTERN_SPECIAL_CHAR = '/[^0-9a-z]/i';
    const PATTERN_MD5 = '/[0-9abcdef]{32,32}/';

    /**
     * This function just return the field value as it is. No transforming,
     * hashing will be done on server-side.
     *
     * @return string JavaScript code for evaluation
     */
    public function returnFieldJS(): string
    {
        return 'return value;';
    }

    /**
     * Function uses Portable PHP Hashing Framework to create a proper password string if needed
     *
     * @param string $value The value that has to be checked.
     * @param string $is_in Is-In String
     * @param integer $set Determines if the field can be set (value correct) or not
     * @param boolean $storeFlashMessageInSession Used only for phpunit issues
     * @return string The new value of the field
     */
    public function evaluateFieldValue(
        string $value,
        $is_in,
        int &$set,
        bool $storeFlashMessageInSession = true
    ): string {
        // if $value is a md5 hash, return the value directly
        if ($this->isMd5($value) || $this->isSalted($value)) {
            return $value;
        }

        $confArr = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['be_secure_pw'];

        /** @var \TYPO3\CMS\Core\DataHandling\DataHandler $tce */
        $tce = Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\DataHandling\DataHandler::class);
        $tce->BE_USER = $GLOBALS['BE_USER'];

        /** @var \TYPO3\CMS\Core\Log\Logger $logger */
        $logger = Utility\GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);

        // get the languages from ext
        /** @var \TYPO3\CMS\Lang\LanguageService $languageService */
        $languageService = Utility\GeneralUtility::makeInstance(LanguageService::class);
        $languageService->init($tce->BE_USER->uc['lang']);
        $languageService->includeLLFile('EXT:be_secure_pw/Resources/Private/Language/locallang.xml');
        /** @var \TYPO3\CMS\Core\Messaging\FlashMessageQueue $flashMessageQueue */
        $flashMessageQueue = Utility\GeneralUtility::makeInstance(
            FlashMessageQueue::class,
            'core.template.flashMessages'
        );
        $set = true;

        $messages = [];
        // check for password length
        $passwordLength = (int)$confArr['passwordLength'];
        if ($confArr['passwordLength'] && $passwordLength && strlen($value) < $confArr['passwordLength']) {
            /* password too short */
            $set = false;
            $logger->error(
                sprintf($languageService->getLL('shortPassword'), $passwordLength)
            );
            $messages[] = sprintf($languageService->getLL('shortPassword'), $passwordLength);
        }

        $counter = 0;
        $notUsed = [];

        $checks = [
            'lowercaseChar' => static::PATTERN_LOWER_CHAR,
            'capitalChar' => static::PATTERN_CAPITAL_CHAR,
            'digit' => static::PATTERN_DIGIT,
            'specialChar' => static::PATTERN_SPECIAL_CHAR,
        ];

        foreach ($checks as $index => $pattern) {
            if ($confArr[$index]) {
                if (preg_match($pattern, $value) > 0) {
                    $counter++;
                } else {
                    $notUsed[] = $languageService->getLL($index);
                }
            }
        }

        if ($counter < $confArr['patterns']) {
            /* password does not fit all conventions */
            $ignoredPatterns = $confArr['patterns'] - $counter;

            $additional = '';
            $set = false;

            if (is_array($notUsed) && !empty($notUsed)) {
                if (count($notUsed) > 1) {
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
            if (!SaltedPasswordsUtility::isUsageEnabled('BE')) {
                $value = md5($value);
            }
            return $value;
        }

        $flashMessageQueue->addMessage(
            new FlashMessage(
                implode(LF, $messages),
                $languageService->getLL('messageTitle'),
                FlashMessage::ERROR,
                $storeFlashMessageInSession
            )
        );

        // if password not valid return empty password
        return '';
    }

    /**
     * @param string $password
     * @return boolean
     */
    private function isMd5(string $password): bool
    {
        return (boolean)preg_match(static::PATTERN_MD5, $password);
    }

    /**
     * @param string $password
     * @return boolean
     */
    private function isSalted(string $password): bool
    {
        if (!SaltedPasswordsUtility::isUsageEnabled('BE')) {
            return false;
        }

        $saltFactory = PasswordHashFactory::getSaltingInstance($password, 'BE');
        if (!$saltFactory) {
            return false;
        }

        return $saltFactory->isValidSaltedPW($password);
    }
}
