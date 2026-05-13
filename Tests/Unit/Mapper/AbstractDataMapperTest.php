<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Mapper;

use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Model\AbstractModel;
use OliverKlee\Oelib\Tests\Unit\Mapper\Fixtures\ColumnLessTestingMapper;
use OliverKlee\Oelib\Tests\Unit\Mapper\Fixtures\ModelLessTestingMapper;
use OliverKlee\Oelib\Tests\Unit\Mapper\Fixtures\TableLessTestingMapper;
use OliverKlee\Oelib\Tests\Unit\Mapper\Fixtures\TestingChildMapper;
use OliverKlee\Oelib\Tests\Unit\Mapper\Fixtures\TestingMapper;
use OliverKlee\Oelib\Tests\Unit\Model\Fixtures\TestingModel;
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

    /**
     * @test
     */
    public function instantiationOfSubclassWithEmptyTableNameThrowsException(): void
    {
        $this->expectException(\TypeError::class);

        new TableLessTestingMapper();
    }

    /**
     * @test
     */
    public function instantiationOfSubclassWithEmptyColumnListThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new ColumnLessTestingMapper();
    }

    /**
     * @test
     */
    public function instantiationOfSubclassWithEmptyModelNameThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new ModelLessTestingMapper();
    }

    //////////////////////////////
    // Tests concerning getModel
    //////////////////////////////

    /**
     * @test
     */
    public function getModelWithArrayWithoutUidElementProvidedThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$data must contain an element "uid".');
        $this->expectExceptionCode(1_331_319_491);

        $this->subject->getModel([]);
    }

    /**
     * @test
     */
    public function getModelWithArrayWithZeroUidThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$data["uid"] must be a positive integer.');
        $this->expectExceptionCode(1_699_655_040);

        $this->subject->getModel(['uid' => 0]);
    }

    /**
     * @test
     */
    public function getModelWithArrayWithNegativeUidThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$data["uid"] must be a positive integer.');
        $this->expectExceptionCode(1_699_655_040);

        $this->subject->getModel(['uid' => -1]);
    }

    // Tests concerning load and reload

    /**
     * @test
     */
    public function loadWithModelWithoutUidThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('load must only be called with models that already have a UID.');
        $this->expectExceptionCode(1_331_319_554);

        $model = new TestingModel();
        $this->subject->load($model);
    }

    /**
     * @test
     */
    public function reloadForTestingOnlyGhostThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('This ghost was created via getNewGhost and must not be loaded.');
        $this->expectExceptionCode(1_331_319_529);

        $model = $this->subject->getNewGhost();
        $this->subject->load($model);
    }

    /**
     * @test
     */
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

    /**
     * @test
     */
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

    /**
     * @test
     */
    public function findWithZeroUidThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$uid must be > 0.');
        $this->expectExceptionCode(1_331_488_761);

        // @phpstan-ignore-next-line We're testing for a contract violation here.
        $this->subject->find(0);
    }

    /**
     * @test
     */
    public function findWithNegativeUidThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$uid must be > 0.');
        $this->expectExceptionCode(1_331_488_761);

        // @phpstan-ignore-next-line We're testing for a contract violation here.
        $this->subject->find(-1);
    }

    /**
     * @test
     */
    public function findWithUidReturnsModelWithThatUid(): void
    {
        $uid = 42;

        self::assertSame(
            $uid,
            $this->subject->find($uid)->getUid(),
        );
    }

    /**
     * @test
     */
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

    /**
     * @test
     */
    public function getNewGhostReturnsModel(): void
    {
        self::assertInstanceOf(AbstractModel::class, $this->subject->getNewGhost());
    }

    /**
     * @test
     */
    public function getNewGhostReturnsModelSpecificToTheMapper(): void
    {
        $result = $this->subject->getNewGhost();

        self::assertInstanceOf(TestingModel::class, $result);
    }

    /**
     * @test
     */
    public function getNewGhostReturnsGhost(): void
    {
        self::assertTrue(
            $this->subject->getNewGhost()->isGhost(),
        );
    }

    /**
     * @test
     */
    public function getNewGhostReturnsModelWithUid(): void
    {
        self::assertTrue(
            $this->subject->getNewGhost()->hasUid(),
        );
    }

    /**
     * @test
     */
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

    /**
     * @test
     */
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

    /**
     * @test
     */
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

    /**
     * @test
     */
    public function findOneByKeyFromCacheForEmptyKeyThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$key must not be empty.');
        $this->expectExceptionCode(1_416_847_364);

        // @phpstan-ignore-next-line We are explicitly testing for a contract violation here.
        $this->subject->findOneByKeyFromCache('', 'bar');
    }

    /**
     * @test
     */
    public function findOneByKeyFromCacheForInexistentKeyThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('"foo" is not a valid key for this mapper.');
        $this->expectExceptionCode(1_331_319_882);

        $this->subject->findOneByKeyFromCache('foo', 'bar');
    }

    /**
     * @test
     */
    public function findOneByKeyFromCacheForEmptyValueThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$value must not be empty.');
        $this->expectExceptionCode(1_331_319_892);

        // @phpstan-ignore-next-line We are explicitly testing for a contract violation here.
        $this->subject->findOneByKeyFromCache('title', '');
    }

    /**
     * @test
     */
    public function findOneByKeyFromCacheForModelNotInCacheThrowsException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Not found');
        $this->expectExceptionCode(1_573_836_483);

        $this->subject->findOneByKeyFromCache('title', 'bar');
    }

    /**
     * @test
     */
    public function findOneByKeyForEmptyKeyThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$key must not be empty.');
        $this->expectExceptionCode(1_416_847_364);

        // @phpstan-ignore-next-line We are explicitly testing for a contract violation here.
        $this->subject->findOneByKey('', 'bar');
    }

    /**
     * @test
     */
    public function findOneByKeyForInexistentKeyThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('"foo" is not a valid key for this mapper.');
        $this->expectExceptionCode(1_331_319_882);

        $this->subject->findOneByKey('foo', 'bar');
    }

    /**
     * @test
     */
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

    /**
     * @test
     */
    public function findAllByRelationWithModelWithoutUidThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$model must have a UID.');
        $this->expectExceptionCode(1_331_319_915);

        (new TestingChildMapper())->findAllByRelation(new TestingModel(), 'parent');
    }

    /**
     * @test
     */
    public function getTableNameReturnsTableName(): void
    {
        self::assertSame(
            'tx_oelib_test',
            $this->subject->getTableName(),
        );
    }
}
