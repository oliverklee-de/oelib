<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Mapper;

use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Oelib\Mapper\FrontEndUserMapper;
use OliverKlee\Oelib\Model\FrontEndUser;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Oelib\Mapper\FrontEndUserMapper
 * @covers \OliverKlee\Oelib\Model\FrontEndUser
 */
final class FrontEndUserMapperTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = ['oliverklee/oelib'];

    private FrontEndUserMapper $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new FrontEndUserMapper();
    }

    // Tests concerning findByUserName

    /**
     * @test
     */
    public function findByUserNameForEmptyUserNameThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$value must not be empty.');

        // @phpstan-ignore-next-line We are explicitly testing for a contract violation here.
        $this->subject->findByUserName('');
    }

    /**
     * @test
     */
    public function findByUserNameWithNameOfExistingUserReturnsFrontEndUserInstance(): void
    {
        $username = 'max.doe';
        $connection = $this->getConnectionPool()->getConnectionForTable('fe_users');
        $connection->insert('fe_users', ['username' => $username]);

        self::assertInstanceOf(
            FrontEndUser::class,
            $this->subject->findByUserName($username),
        );
    }

    /**
     * @test
     */
    public function findByUserNameWithNameOfExistingUserReturnsModelWithThatUid(): void
    {
        $username = 'max.doe';
        $connection = $this->getConnectionPool()->getConnectionForTable('fe_users');
        $connection->insert('fe_users', ['username' => $username]);

        $uid = (int)$connection->lastInsertId('fe_users');

        self::assertSame(
            $uid,
            $this->subject->findByUserName($username)->getUid(),
        );
    }

    /**
     * @test
     */
    public function findByUserNameWithUppercasedNameOfExistingUppercasedUserReturnsModelWithThatUid(): void
    {
        $username = 'MAX.DOE';
        $connection = $this->getConnectionPool()->getConnectionForTable('fe_users');
        $connection->insert('fe_users', ['username' => $username]);

        $uid = (int)$connection->lastInsertId('fe_users');

        self::assertSame(
            $uid,
            $this->subject->findByUserName($username)->getUid(),
        );
    }

    /**
     * @test
     */
    public function findByUserNameWithNameOfNonExistentUserThrowsException(): void
    {
        $this->expectException(NotFoundException::class);

        $username = 'max.doe';
        $connection = $this->getConnectionPool()->getConnectionForTable('fe_users');
        $connection->insert('fe_users', ['username' => $username, 'deleted' => 1]);

        $this->subject->findByUserName($username);
    }
}
