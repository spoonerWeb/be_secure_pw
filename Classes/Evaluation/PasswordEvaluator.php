<?php

declare(strict_types=1);

namespace SpoonerWeb\BeSecurePw\Evaluation;

/**
 * This file is part of the TYPO3 CMS extension "be_secure_pw".
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

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class PasswordEvaluator
 *
 * @author Thomas Loeffler <loeffler@spooner-web.de>
 */
class PasswordEvaluator implements SingletonInterface
{
    public const PATTERN_LOWER_CHAR = '/[a-z]/';
    public const PATTERN_CAPITAL_CHAR = '/[A-Z]/';
    public const PATTERN_DIGIT = '/[0-9]/';
    public const PATTERN_SPECIAL_CHAR = '/[^0-9a-z]/i';

    protected LanguageServiceFactory $languageServiceFactory;
    protected BackendUserAuthentication $backendUser;

    public function __construct(LanguageServiceFactory $languageServiceFactory, BackendUserAuthentication $backendUser)
    {
        $this->languageServiceFactory = $languageServiceFactory;
        $this->backendUser = $backendUser;
    }

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
     * @param int $set Determines if the field can be set (value correct) or not
     * @param bool $storeFlashMessageInSession Used only for phpunit issues
     * @return string The new value of the field
     * @throws \TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException
     * @throws \TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException
     */
    public function evaluateFieldValue(
        string $value,
        string $is_in,
        int &$set,
        bool $storeFlashMessageInSession = true
    ): string {
        $extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('be_secure_pw');

        /** @var \TYPO3\CMS\Core\Log\Logger $logger */
        $logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);

        $languageService = $this->languageServiceFactory->create($this->backendUser->uc['lang'] ?? 'default');
        $languageService->includeLLFile('EXT:be_secure_pw/Resources/Private/Language/locallang.xlf');

        $set = 1;

        $messages = [];
        // check for password length
        $passwordLength = $extConf['passwordLength'] ?? 0;
        $passwordLengthValue = (int)$passwordLength;
        if ($passwordLengthValue && strlen($value) < $passwordLengthValue) {
            /* password too short */
            $set = 0;

            $passwordToShortString = $languageService->getLL('shortPassword') ?? '';
            $logger->error(sprintf($passwordToShortString, $passwordLengthValue));
            $messages[] = sprintf($passwordToShortString, $passwordLengthValue);
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
            $checkActive = $extConf[$index] ?? false;
            if ($checkActive) {
                if (preg_match($pattern, $value) > 0) {
                    $counter++;
                } else {
                    $notUsed[] = $languageService->getLL($index);
                }
            }
        }

        $patterns = $extConf['patterns'] ?? 0;
        if ($counter < $patterns) {
            /* password does not fit all conventions */
            $ignoredPatterns = $patterns - $counter;

            $additional = '';
            $set = false;

            if (is_array($notUsed) && !empty($notUsed)) {
                if (count($notUsed) > 1) {
                    $notUsedConventions = $languageService->getLL('notUsedConventions') ?? '';
                    $additional = sprintf($notUsedConventions, implode(', ', $notUsed));
                } else {
                    $notUsedConvention = $languageService->getLL('notUsedConvention') ?? '';
                    $additional = sprintf($notUsedConvention, $notUsed[0] ?? '');
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
            return $value;
        }

        if ($storeFlashMessageInSession) {
            $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
            $messageQueue = $flashMessageService->getMessageQueueByIdentifier();
            foreach ($messages as $message) {
                $flashMessage = GeneralUtility::makeInstance(
                    FlashMessage::class,
                    $message,
                    $languageService->getLL('passwordNotChanged'),
                    FlashMessage::ERROR,
                    $storeFlashMessageInSession
                );
                $messageQueue->addMessage($flashMessage);
            }
        }

        // if password not valid return empty password
        return '';
    }
}
