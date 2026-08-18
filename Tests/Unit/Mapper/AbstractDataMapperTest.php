<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Mapper;

use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Oelib\Model\AbstractModel;
use OliverKlee\Oelib\Tests\Unit\Mapper\Fixtures\ColumnLessTestingMapper;
use OliverKlee\Oelib\Tests\Unit\Mapper\Fixtures\ModelLessTestingMapper;
use OliverKlee\Oelib\Tests\Unit\Mapper\Fixtures\TableLessTestingMapper;
use OliverKlee\Oelib\Tests\Unit\Mapper\Fixtures\TestingChildMapper;
use OliverKlee\Oelib\Tests\Unit\Mapper\Fixtures\TestingMapper;
use OliverKlee\Oelib\Tests\Unit\Model\Fixtures\TestingModel;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Oelib\Mapper\AbstractDataMapper
 */
final class AbstractDataMapperTest extends UnitTestCase
{
    private TestingMapper $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new TestingMapper();
    }

    #[Test]
    public function instantiationOfSubclassWithEmptyTableNameThrowsException(): void
    {
        $this->expectException(\TypeError::class);

        new TableLessTestingMapper();
    }

    #[Test]
    public function instantiationOfSubclassWithEmptyColumnListThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new ColumnLessTestingMapper();
    }

    #[Test]
    public function instantiationOfSubclassWithEmptyModelNameThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new ModelLessTestingMapper();
    }

    //////////////////////////////
    // Tests concerning getModel
    //////////////////////////////

    #[Test]
    public function getModelWithArrayWithoutUidElementProvidedThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$data must contain an element "uid".');
        $this->expectExceptionCode(1_331_319_491);

        $this->subject->getModel([]);
    }

    #[Test]
    public function getModelWithArrayWithZeroUidThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$data["uid"] must be a positive integer.');
        $this->expectExceptionCode(1_699_655_040);

        $this->subject->getModel(['uid' => 0]);
    }

    #[Test]
    public function getModelWithArrayWithNegativeUidThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$data["uid"] must be a positive integer.');
        $this->expectExceptionCode(1_699_655_040);

        $this->subject->getModel(['uid' => -1]);
    }

    // Tests concerning load and reload

    #[Test]
    public function loadWithModelWithoutUidThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('load must only be called with models that already have a UID.');
        $this->expectExceptionCode(1_331_319_554);

        $model = new TestingModel();
        $this->subject->load($model);
    }

    #[Test]
    public function reloadForTestingOnlyGhostThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('This ghost was created via getNewGhost and must not be loaded.');
        $this->expectExceptionCode(1_331_319_529);

        $model = $this->subject->getNewGhost();
        $this->subject->load($model);
    }

    #[Test]
    public function reloadForModelWithoutUidThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('load must only be called with models that already have a UID.');
        $this->expectExceptionCode(1_331_319_554);

        $model = new TestingModel();
        $this->subject->load($model);
    }

    //////////////////////////////////////
    // Tests concerning the model states
    //////////////////////////////////////

    #[Test]
    public function findInitiallyReturnsGhostModel(): void
    {
        $uid = 42;

        self::assertTrue(
            $this->subject->find($uid)->isGhost(),
        );
    }

    //////////////////////////
    // Tests concerning find
    //////////////////////////

    #[Test]
    public function findWithZeroUidThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$uid must be > 0.');
        $this->expectExceptionCode(1_331_488_761);

        // @phpstan-ignore-next-line We're testing for a contract violation here.
        $this->subject->find(0);
    }

    #[Test]
    public function findWithNegativeUidThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$uid must be > 0.');
        $this->expectExceptionCode(1_331_488_761);

        // @phpstan-ignore-next-line We're testing for a contract violation here.
        $this->subject->find(-1);
    }

    #[Test]
    public function findWithUidReturnsModelWithThatUid(): void
    {
        $uid = 42;

        self::assertSame(
            $uid,
            $this->subject->find($uid)->getUid(),
        );
    }

    #[Test]
    public function findWithUidCalledTwoTimesReturnsSameModel(): void
    {
        $uid = 42;

        self::assertSame(
            $this->subject->find($uid),
            $this->subject->find($uid),
        );
    }

    /////////////////////////////////
    // Tests concerning getNewGhost
    /////////////////////////////////

    #[Test]
    public function getNewGhostReturnsModel(): void
    {
        self::assertInstanceOf(AbstractModel::class, $this->subject->getNewGhost());
    }

    #[Test]
    public function getNewGhostReturnsModelSpecificToTheMapper(): void
    {
        $result = $this->subject->getNewGhost();

        self::assertInstanceOf(TestingModel::class, $result);
    }

    #[Test]
    public function getNewGhostReturnsGhost(): void
    {
        self::assertTrue(
            $this->subject->getNewGhost()->isGhost(),
        );
    }

    #[Test]
    public function getNewGhostReturnsModelWithUid(): void
    {
        self::assertTrue(
            $this->subject->getNewGhost()->hasUid(),
        );
    }

    #[Test]
    public function getNewGhostCreatesRegisteredModel(): void
    {
        $ghost = $this->subject->getNewGhost();
        $uid = $ghost->getUid();
        \assert($uid >= 1);

        self::assertSame(
            $ghost,
            $this->subject->find($uid),
        );
    }

    #[Test]
    public function loadingAGhostCreatedWithGetNewGhostThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('This ghost was created via getNewGhost and must not be loaded.');
        $this->expectExceptionCode(1_331_319_529);

        $ghost = $this->subject->getNewGhost();
        $this->subject->load($ghost);
    }

    ////////////////////////////////////////////////
    // Tests concerning findSingleByWhereClause().
    ////////////////////////////////////////////////

    #[Test]
    public function findSingleByWhereClauseWithEmptyWhereClausePartsThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The parameter $whereClauseParts must not be empty.');
        $this->expectExceptionCode(1_331_319_506);

        // @phpstan-ignore-next-line We are explicitly testing for a contract violation here.
        $this->subject->findSingleByWhereClause([]);
    }

    /////////////////////////////////////
    // Tests concerning additional keys
    /////////////////////////////////////

    #[Test]
    public function findOneByKeyFromCacheForEmptyKeyThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$key must not be empty.');
        $this->expectExceptionCode(1_416_847_364);

        // @phpstan-ignore-next-line We are explicitly testing for a contract violation here.
        $this->subject->findOneByKeyFromCache('', 'bar');
    }

    #[Test]
    public function findOneByKeyFromCacheForInexistentKeyThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('"foo" is not a valid key for this mapper.');
        $this->expectExceptionCode(1_331_319_882);

        $this->subject->findOneByKeyFromCache('foo', 'bar');
    }

    #[Test]
    public function findOneByKeyFromCacheForEmptyValueThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$value must not be empty.');
        $this->expectExceptionCode(1_331_319_892);

        // @phpstan-ignore-next-line We are explicitly testing for a contract violation here.
        $this->subject->findOneByKeyFromCache('title', '');
    }

    #[Test]
    public function findOneByKeyFromCacheForModelNotInCacheThrowsException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Not found');
        $this->expectExceptionCode(1_573_836_483);

        $this->subject->findOneByKeyFromCache('title', 'bar');
    }

    #[Test]
    public function findOneByKeyForEmptyKeyThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$key must not be empty.');
        $this->expectExceptionCode(1_416_847_364);

        // @phpstan-ignore-next-line We are explicitly testing for a contract violation here.
        $this->subject->findOneByKey('', 'bar');
    }

    #[Test]
    public function findOneByKeyForInexistentKeyThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('"foo" is not a valid key for this mapper.');
        $this->expectExceptionCode(1_331_319_882);

        $this->subject->findOneByKey('foo', 'bar');
    }

    #[Test]
    public function findOneByKeyForEmptyValueThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$value must not be empty.');
        $this->expectExceptionCode(1_331_319_892);

        // @phpstan-ignore-next-line We are explicitly testing for a contract violation here.
        $this->subject->findOneByKey('title', '');
    }

    ///////////////////////////////////////
    // Tests concerning findAllByRelation
    ///////////////////////////////////////

    #[Test]
    public function findAllByRelationWithModelWithoutUidThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$model must have a UID.');
        $this->expectExceptionCode(1_331_319_915);

        $model = new TestingModel();

        $subject = new TestingChildMapper();

        $subject->findAllByRelation($model, 'parent');
    }

    #[Test]
    public function getTableNameReturnsTableName(): void
    {
        self::assertSame(
            'tx_oelib_test',
            $this->subject->getTableName(),
        );
    }
}
