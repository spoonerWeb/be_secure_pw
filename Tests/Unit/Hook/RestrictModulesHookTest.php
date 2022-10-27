<?php

declare(strict_types=1);

namespace SpoonerWeb\BeSecurePw\Tests\Unit\Hook;

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

use Doctrine\DBAL\Result;
use SpoonerWeb\BeSecurePw\Hook\RestrictModulesHook;
use TYPO3\CMS\Backend\Controller\BackendController;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Expression\ExpressionBuilder;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\QueryRestrictionContainerInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case.
 *
 * @author Mario Lubenka <mario.lubenka@dkd.de>
 */
class RestrictModulesHookTest extends UnitTestCase
{
    /**
     * @var RestrictModulesHook
     */
    protected $subject;

    public function setUp(): void
    {
        $this->subject = new RestrictModulesHook();
        $this->resetSingletonInstances = true;
    }

    /**
     * Test if start module is changed when password is expired
     *
     * @test
     * @dataProvider changeStartModuleProvider
     */
    public function changeStartModule(
        array $userData,
        string $expectedModule
    ): void {
        $GLOBALS['BE_USER'] = new BackendUserAuthentication();
        $GLOBALS['BE_USER']->uc = [
            'startModule' => 'other_module',
        ];
        $GLOBALS['BE_USER']->user['uid'] = 123;
        $GLOBALS['BE_USER']->user['username'] = 'test';

        $this->prepareMockedUser($userData);

        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['be_secure_pw'] = [
            'ignorePasswordChangeForAdmins' => false,
            'validUntil' => '1 day',
        ];

        $this->subject->renderPreProcess([], $this->createMock(BackendController::class));
        if (!$expectedModule) {
            self::assertArrayNotHasKey(
                'startModuleOnFirstLogin',
                $GLOBALS['BE_USER']->uc
            );
        } else {
            self::assertEquals(
                $expectedModule,
                $GLOBALS['BE_USER']->uc['startModuleOnFirstLogin']
            );
        }

        // Ensure regular startModule does not change.
        // TYPO3 will use startModuleOnFirstLogin first if set
        self::assertEquals(
            'other_module',
            $GLOBALS['BE_USER']->uc['startModule']
        );
    }

    /**
     * This method will set up all required mocks
     * to return $userData in be_users record fetched via QueryBuilder.
     * @param array $userData
     */
    protected function prepareMockedUser(array $userData)
    {
        $GLOBALS['TCA']['be_users'] = true;
        // Mock QueryBuilder
        $queryBuilderMock = $this->createMock(QueryBuilder::class);
        $queryBuilderMock->method('getRestrictions')->willReturn(
            $this->createMock(QueryRestrictionContainerInterface::class)
        );
        $queryBuilderMock->method('expr')->willReturn(
            $this->createMock(ExpressionBuilder::class)
        );
        $queryBuilderMock->method('select')->willReturnSelf();
        $queryBuilderMock->method('from')->willReturnSelf();
        $queryBuilderMock->method('where')->willReturnSelf();
        $queryBuilderMock->method('andWhere')->willReturnSelf();

        $resultMock = $this->createMock(Result::class);
        $resultMock->method('fetchAssociative')->willReturn($userData);
        $queryBuilderMock->method('executeQuery')->willReturn($resultMock);

        $connectionPoolMock = $this->createMock(ConnectionPool::class);
        $connectionPoolMock->method('getQueryBuilderForTable')
            ->with('be_users')
            ->willReturn($queryBuilderMock);
        GeneralUtility::addInstance(ConnectionPool::class, $connectionPoolMock);
    }

    /**
     * @return array<array>
     */
    public function changeStartModuleProvider(): array
    {
        return [
            'startModuleIsChangedWhenPasswordExpired' => [
                ['tx_besecurepw_lastpwchange' => 0, 'lastlogin' => 0],
                'user_setup',
            ],
            'startModuleIsNotChangedWhenPasswordIsNotExpired' => [
                ['tx_besecurepw_lastpwchange' => time()-(3600*23), 'lastlogin' => time()],
                '',
            ],
        ];
    }
}
