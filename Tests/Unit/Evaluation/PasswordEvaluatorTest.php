<?php
namespace SpoonerWeb\BeSecurePw\Tests\Unit\Evaluator;

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
 * Test case
 *
 * @author Thomas Löffler <loeffler@spooner-web.de>
 */
class PasswordEvaluatorTest extends \TYPO3\CMS\Core\Tests\BaseTestCase
{

    /**
     * @var \SpoonerWeb\BeSecurePw\Evaluation\PasswordEvaluator
     */
    protected $subject;

    /**
     *
     */
    public function setUp()
    {
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

}