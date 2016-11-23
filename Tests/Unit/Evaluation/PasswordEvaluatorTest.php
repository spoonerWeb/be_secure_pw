<?php
namespace SpoonerWeb\BeSecurePw\Tests\Unit\Evaluation;

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

/**
 * Test case.
 *
 * @author Thomas Löffler <loeffler@spooner-web.de>
 */
class PasswordEvaluatorTest extends \TYPO3\CMS\Core\Tests\Unit\Resource\BaseTestCase {

	/**
	 * @var \SpoonerWeb\BeSecurePw\Evaluation\PasswordEvaluator
	 */
	protected $subject = NULL;

	public function setUp() {
		$this->subject = new \SpoonerWeb\BeSecurePw\Evaluation\PasswordEvaluator();
	}

    /**
     * @test
     * @return void
     */
    public function classCanBeInstantiated()
    {
        self::assertInstanceOf(
            \SpoonerWeb\BeSecurePw\Evaluation\PasswordEvaluator::class,
            $this->subject
        );
    }

	/**
	 * @test
	 */
	public function returnFieldJavaScriptReturnsDefaultString() {
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
	public function checkForValidPassword(array $configuration, $password) {
		$set = TRUE;
		$GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['be_secure_pw'] = serialize($configuration);
		self::assertEquals(
			$password,
			$this->subject->evaluateFieldValue($password, '', $set)
		);
	}

	/**
	 * @return array
	 */
	public function validPasswordDataProvider() {
		return array(
			'passwordContainingFourLowerCharactersWithoutConfigurationIsValid' => array(
				array(),
				'test'
			),
			'passwordContainingTwelveLowerCharactersWithConfigOfMinimumEightCharactersIsValid' => array(
				array(
					'passwordLength' => 8
				),
				'testpassword'
			),
			'passwordContainingTwelveLowerCharactersWithConfigOfMinimumEightCharactersAndLowerCharactersIsValid' => array(
				array(
					'passwordLength' => 8,
					'lowercaseChar' => TRUE
				),
				'testpassword'
			),
			'passwordContainingTwelveUpperAndLowerCharactersWithConfigOfMinimumEightCharactersAndCapitalCharactersIsValid' => array(
				array(
					'passwordLength' => 8,
					'capitalChar' => TRUE,
					'patterns' => 1
				),
				'testPassword'
			),
			'passwordContainingTwelveUpperAndLowerCharactersWithConfigOfMinimumEightCharactersDigitsOrCapitalCharactersIsValid' => array(
				array(
					'passwordLength' => 8,
					'capitalChar' => TRUE,
					'digit' => TRUE,
					'patterns' => 1
				),
				'testPassword'
			),
			'passwordContainingUpperLowerDigitsAndSpecialCharactersWith22CharactersWithHardestConfigAndMinimumTwelveCharactersIsValid' => array(
				array(
					'passwordLength' => 12,
					'capitalChar' => TRUE,
					'lowercaseChar' => TRUE,
					'digit' => TRUE,
					'specialChar' => TRUE,
					'patterns' => 4
				),
				'Ycb&T8bdHUCP[zD6HqB7pM'
			)
		);
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
	public function checkForInvalidPassword(array $configuration, $password) {
		$set = TRUE;
		$GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['be_secure_pw'] = serialize($configuration);
		self::assertEquals(
			'',
			$this->subject->evaluateFieldValue($password, '', $set)
		);
	}

	/**
	 * @return array
	 */
	public function invalidPasswordDataProvider() {
		return array(
			'emptyPasswordWithoutConfigurationIsInvalid' => array(
				array(),
				''
			),
			'passwordContainingFourLowerCharactersWithConfigOfMinimumEightCharactersIsInvalid' => array(
				array(
					'passwordLength' => 8
				),
				'test'
			),
			'passwordContainingTwelveLowerCharactersWithConfigOfMinimumEightCharactersAndCapitalCharactersIsInvalid' => array(
				array(
					'passwordLength' => 8,
					'capitalChar' => TRUE,
					'patterns' => 1
				),
				'testpassword'
			),
			'passwordContainingTwelveUpperAndLowerCharactersWithConfigOfMinimumEightCharactersDigitsAndCapitalCharactersIsInvalid' => array(
				array(
					'passwordLength' => 8,
					'capitalChar' => TRUE,
					'digit' => TRUE,
					'patterns' => 2
				),
				'testPassword'
			),
		);
	}
}