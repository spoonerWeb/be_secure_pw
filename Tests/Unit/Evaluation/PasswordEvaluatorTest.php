<?php
declare(strict_types=1);

namespace SpoonerWeb\BeSecurePw\Tests\Unit\Evaluator;

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

use SpoonerWeb\BeSecurePw\Evaluation\PasswordEvaluator;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case.
 *
 * @author Thomas Löffler <loeffler@spooner-web.de>
 */
class PasswordEvaluatorTest extends UnitTestCase
{
    /**
     * @var \SpoonerWeb\BeSecurePw\Evaluation\PasswordEvaluator
     */
    protected $subject;

    public function setUp(): void
    {
        $languageServiceFactory = self::getMockBuilder(LanguageServiceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $backendUser = self::getMockBuilder(BackendUserAuthentication::class)
            ->getMock();

        $this->subject = GeneralUtility::makeInstance(
            PasswordEvaluator::class,
            $languageServiceFactory,
            $backendUser
        );

        $this->resetSingletonInstances = true;
    }

    /**
     * @test
     */
    public function classCanBeInstantiated()
    {
        self::assertInstanceOf(
            PasswordEvaluator::class,
            $this->subject
        );
    }

    /**
     * @test
     */
    public function returnFieldJavaScriptReturnsDefaultString()
    {
        self::assertEquals(
            'return value;',
            $this->subject->returnFieldJS()
        );
    }

    /**
     * Test for valid passwords.
     * If password is valid, the password will be returned.
     *
     * @test
     * @param array $configuration
     * @param string $password
     * @dataProvider validPasswordDataProvider
     */
    public function checkForValidPassword(array $configuration, string $password)
    {
        $set = 1;
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['be_secure_pw'] = $configuration;
        self::assertEquals(
            $password,
            $this->subject->evaluateFieldValue($password, '', $set)
        );
    }

    /**
     * @return array
     */
    public function validPasswordDataProvider()
    {
        return [
            'passwordContainingFourLowerCharactersWithoutConfigurationIsValid' => [
                [],
                'test',
            ],
            'passwordContainingTwelveLowerCharactersWithConfigOfMinimumEightCharactersIsValid' => [
                [
                    'passwordLength' => 8,
                ],
                'testpassword',
            ],
            'passwordContainingTwelveLowerCharactersWithConfigOfMinimumEightCharactersAndLowerCharactersIsValid' => [
                [
                    'passwordLength' => 8,
                    'lowercaseChar' => true,
                ],
                'testpassword',
            ],
            // @codingStandardsIgnoreLine
            'passwordContainingTwelveUpperAndLowerCharactersWithConfigOfMinimumEightCharactersAndCapitalCharactersIsValid' => [
                [
                    'passwordLength' => 8,
                    'capitalChar' => true,
                    'patterns' => 1,
                ],
                'testPassword',
            ],
            // @codingStandardsIgnoreLine
            'passwordContainingTwelveUpperAndLowerCharactersWithConfigOfMinimumEightCharactersDigitsOrCapitalCharactersIsValid' => [
                [
                    'passwordLength' => 8,
                    'capitalChar' => true,
                    'digit' => true,
                    'patterns' => 1,
                ],
                'testPassword',
            ],
            // @codingStandardsIgnoreLine
            'passwordContainingUpperLowerDigitsAndSpecialCharactersWith22CharactersWithHardestConfigAndMinimumTwelveCharactersIsValid' => [
                [
                    'passwordLength' => 12,
                    'capitalChar' => true,
                    'lowercaseChar' => true,
                    'digit' => true,
                    'specialChar' => true,
                    'patterns' => 4,
                ],
                'Ycb&T8bdHUCP[zD6HqB7pM',
            ],
        ];
    }

    /**
     * Test for invalid passwords.
     * If the password is invalid an empty string will be returned.
     *
     * @test
     * @param array $configuration
     * @param string $password
     * @dataProvider invalidPasswordDataProvider
     */
    public function checkForInvalidPassword(array $configuration, string $password)
    {
        $set = 1;
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['be_secure_pw'] = $configuration;
        self::assertEquals(
            '',
            $this->subject->evaluateFieldValue($password, '', $set, false)
        );
    }

    /**
     * @return array
     */
    public function invalidPasswordDataProvider()
    {
        return [
            'emptyPasswordWithoutConfigurationIsInvalid' => [
                [],
                '',
            ],
            'passwordContainingFourLowerCharactersWithConfigOfMinimumEightCharactersIsInvalid' => [
                [
                    'passwordLength' => 8,
                ],
                'test',
            ],
            // @codingStandardsIgnoreLine
            'passwordContainingTwelveLowerCharactersWithConfigOfMinimumEightCharactersAndCapitalCharactersIsInvalid' => [
                [
                    'passwordLength' => 8,
                    'capitalChar' => true,
                    'patterns' => 1,
                ],
                'testpassword',
            ],
            // @codingStandardsIgnoreLine
            'passwordContainingTwelveUpperAndLowerCharactersWithConfigOfMinimumEightCharactersDigitsAndCapitalCharactersIsInvalid' => [
                [
                    'passwordLength' => 8,
                    'capitalChar' => true,
                    'digit' => true,
                    'patterns' => 2,
                ],
                'testPassword',
            ],
        ];
    }
}
