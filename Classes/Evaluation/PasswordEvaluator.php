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
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Saltedpasswords\Utility\SaltedPasswordsUtility;
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
     * @throws \TYPO3\CMS\Core\Crypto\PasswordHashing\InvalidPasswordHashException
     */
    public function evaluateFieldValue(
        string $value,
        $is_in,
        int &$set,
        bool $storeFlashMessageInSession = true
    ): string {

        $extConf = \SpoonerWeb\BeSecurePw\Configuration\ExtensionConfiguration::getExtensionConfig();

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
        $passwordLength = (int)$extConf['passwordLength'];
        if ($extConf['passwordLength'] && $passwordLength && strlen($value) < $extConf['passwordLength']) {
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
            if ($extConf[$index]) {
                if (preg_match($pattern, $value) > 0) {
                    $counter++;
                } else {
                    $notUsed[] = $languageService->getLL($index);
                }
            }
        }

        if ($counter < $extConf['patterns']) {
            /* password does not fit all conventions */
            $ignoredPatterns = $extConf['patterns'] - $counter;

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
            // Hash password before storing it
            $hashInstance = Utility\GeneralUtility::makeInstance(PasswordHashFactory::class)->getDefaultHashInstance('BE');
            if ($hashInstance->isHashUpdateNeeded($value)) {
                $value = $hashInstance->getHashedPassword($value);
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
}
