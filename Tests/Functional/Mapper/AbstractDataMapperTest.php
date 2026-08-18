<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Mapper;

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Model\AbstractModel;
use OliverKlee\Oelib\Model\FrontEndUser;
use OliverKlee\Oelib\Tests\Unit\Mapper\Fixtures\TestingChildMapper;
use OliverKlee\Oelib\Tests\Unit\Mapper\Fixtures\TestingMapper;
use OliverKlee\Oelib\Tests\Unit\Model\Fixtures\TestingChildModel;
use OliverKlee\Oelib\Tests\Unit\Model\Fixtures\TestingModel;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\DateTimeAspect;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Oelib\Mapper\AbstractDataMapper
 * @covers \OliverKlee\Oelib\Model\AbstractModel
 *
 * @phpstan-type DatabaseColumn string|int|float|bool|null
 * @phpstan-type DatabaseRow array<string, DatabaseColumn>
 */
final class AbstractDataMapperTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = ['oliverklee/oelib'];

    private MapperRegistry $mapperRegistry;

    private TestingMapper $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $dateAspect = new DateTimeAspect(new \DateTimeImmutable('2018-04-26 12:42:23'));
        $this->get(Context::class)->setAspect('date', $dateAspect);
        $this->mapperRegistry = $this->get(MapperRegistry::class);

        $this->subject = $this->mapperRegistry->getByClassName(TestingMapper::class);
    }

    // Tests concerning load

    #[Test]
    public function loadWithModelWithExistingUidFillsModelWithData(): void
    {
        $title = 'Assassin of Kings';
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['title' => $title]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        $model = new TestingModel();
        $model->setUid($uid);

        $this->subject->load($model);

        self::assertSame($title, $model->getTitle());
    }

    // Tests concerning find

    #[Test]
    public function findWithUidOfExistingRecordReturnsModelDataFromDatabase(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['title' => 'foo']);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertSame('foo', $model->getTitle());
    }

    //////////////////////////////
    // Tests concerning getModel
    //////////////////////////////

    #[Test]
    public function getModelForNonMappedUidReturnsModelInstance(): void
    {
        self::assertInstanceOf(
            AbstractModel::class,
            $this->subject->getModel(['uid' => 2]),
        );
    }

    #[Test]
    public function getModelForNonMappedUidReturnsLoadedModel(): void
    {
        self::assertTrue(
            $this->subject->getModel(['uid' => 2])->isLoaded(),
        );
    }

    #[Test]
    public function getModelForMappedUidOfGhostReturnsModelInstance(): void
    {
        $mappedUid = $this->subject->getNewGhost()->getUid();

        self::assertInstanceOf(
            AbstractModel::class,
            $this->subject->getModel(['uid' => $mappedUid]),
        );
    }

    #[Test]
    public function getModelForMappedUidOfGhostReturnsLoadedModel(): void
    {
        $mappedUid = $this->subject->getNewGhost()->getUid();

        self::assertTrue(
            $this->subject->getModel(['uid' => $mappedUid])->isLoaded(),
        );
    }

    #[Test]
    public function getModelForMappedUidOfGhostReturnsLoadedModelWithTheProvidedData(): void
    {
        $mappedModel = $this->subject->getNewGhost();

        /** @var TestingModel $model */
        $model = $this->subject->getModel(['uid' => $mappedModel->getUid(), 'title' => 'new title']);
        self::assertSame(
            'new title',
            $model->getTitle(),
        );
    }

    #[Test]
    public function getModelForMappedUidOfGhostReturnsThatModel(): void
    {
        $mappedModel = $this->subject->getNewGhost();

        self::assertSame(
            $mappedModel,
            $this->subject->getModel(['uid' => $mappedModel->getUid()]),
        );
    }

    #[Test]
    public function getModelForMappedUidOfLoadedModelReturnsThatModelInstance(): void
    {
        $mappedModel = $this->subject->getNewGhost();
        $mappedModel->setData(['title' => 'foo']);

        self::assertSame(
            $mappedModel,
            $this->subject->getModel(['uid' => $mappedModel->getUid()]),
        );
    }

    #[Test]
    public function getModelForMappedUidOfLoadedModelAndNoNewDataProvidedReturnsModelWithTheInitialData(): void
    {
        $mappedModel = $this->subject->getNewGhost();
        $mappedModel->setData(['title' => 'foo']);

        /** @var TestingModel $model */
        $model = $this->subject->getModel(['uid' => $mappedModel->getUid()]);
        self::assertSame(
            'foo',
            $model->getTitle(),
        );
    }

    #[Test]
    public function getModelForMappedUidOfLoadedModelAndNewDataProvidedReturnsModelWithTheInitialData(): void
    {
        $mappedModel = $this->subject->getNewGhost();
        $mappedModel->setData(['title' => 'foo']);

        /** @var TestingModel $model */
        $model = $this->subject->getModel(['uid' => $mappedModel->getUid(), 'title' => 'new title']);
        self::assertSame(
            'foo',
            $model->getTitle(),
        );
    }

    #[Test]
    public function getModelForMappedUidOfDeadModelReturnsDeadModel(): void
    {
        $mappedModel = $this->subject->getNewGhost();
        $mappedModel->markAsDead();

        self::assertTrue(
            $this->subject->getModel(['uid' => $mappedModel->getUid()])->isDead(),
        );
    }

    #[Test]
    public function getModelForNonMappedUidReturnsModelWithChildrenList(): void
    {
        /** @var TestingModel $model */
        $model = $this->subject->getModel(['uid' => 2]);
        self::assertInstanceOf(
            Collection::class,
            $model->getChildren(),
        );
    }

    #[Test]
    public function getModelSavesModelToCacheByKeys(): void
    {
        $model = $this->subject->getModel(['uid' => 2]);

        self::assertSame(
            [$model],
            $this->subject->getCachedModels(),
        );
    }

    /////////////////////////////////////
    // Tests concerning getListOfModels
    /////////////////////////////////////

    #[Test]
    public function getListOfModelsReturnsInstanceOfList(): void
    {
        self::assertInstanceOf(
            Collection::class,
            $this->subject->getListOfModels([['uid' => 1]]),
        );
    }

    #[Test]
    public function getListOfModelsForAnEmptyArrayProvidedReturnsEmptyList(): void
    {
        self::assertTrue(
            $this->subject->getListOfModels([])->isEmpty(),
        );
    }

    #[Test]
    public function getListOfModelsForOneRecordsProvidedReturnsListWithOneElement(): void
    {
        self::assertCount(1, $this->subject->getListOfModels([['uid' => 1]]));
    }

    #[Test]
    public function getListOfModelsForTwoRecordsProvidedReturnsListWithTwoElements(): void
    {
        self::assertCount(2, $this->subject->getListOfModels([['uid' => 1], ['uid' => 2]]));
    }

    #[Test]
    public function getListOfModelsReturnsListOfModelInstances(): void
    {
        self::assertInstanceOf(
            AbstractModel::class,
            $this->subject->getListOfModels([['uid' => 1]])->current(),
        );
    }

    #[Test]
    public function getListOfModelsReturnsListOfModelWithProvidedTitle(): void
    {
        /** @var Collection<TestingModel> $models */
        $models = $this->subject->getListOfModels([['uid' => 1, 'title' => 'foo']]);

        /** @var TestingModel $current */
        $current = $models->current();
        self::assertSame('foo', $current->getTitle());
    }

    // Tests concerning load and reload

    #[Test]
    public function loadWithModelWithExistingUidOfHiddenRecordMarksModelAsLoaded(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['hidden' => 1]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        $model = new TestingModel();
        $model->setUid($uid);

        $this->subject->load($model);

        self::assertTrue(
            $model->isLoaded(),
        );
    }

    #[Test]
    public function loadForModelWithExistingUidMarksModelAsClean(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['title' => 'foo']);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        $model = new TestingModel();
        $model->setUid($uid);

        $this->subject->load($model);

        self::assertFalse(
            $model->isDirty(),
        );
    }

    #[Test]
    public function loadCanReadFloatDataFromFloatColumn(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['float_data' => 12.5]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        $model = new TestingModel();
        $model->setUid($uid);

        $this->subject->load($model);

        self::assertSame(
            12.5,
            $model->getFloatFromFloatData(),
        );
    }

    #[Test]
    public function loadCanReadFloatDataFromDecimalColumn(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['decimal_data' => 12.5]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        $model = new TestingModel();
        $model->setUid($uid);

        $this->subject->load($model);

        self::assertSame(
            12.5,
            $model->getFloatFromDecimalData(),
        );
    }

    #[Test]
    public function loadCanReadFloatDataFromStringColumn(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['string_data' => '12.5']);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        $model = new TestingModel();
        $model->setUid($uid);

        $this->subject->load($model);

        self::assertSame(
            12.5,
            $model->getFloatFromStringData(),
        );
    }

    //////////////////////////////////////
    // Tests concerning the model states
    //////////////////////////////////////

    #[Test]
    public function findAndAccessingDataLoadsModel(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['title' => 'foo']);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->getTitle();

        self::assertTrue(
            $model->isLoaded(),
        );
    }

    #[Test]
    public function isHiddenOnGhostInDatabaseLoadsModel(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        $model = $this->subject->find($uid);
        $model->isHidden();

        self::assertTrue(
            $model->isLoaded(),
        );
    }

    #[Test]
    public function isHiddenOnGhostNotInDatabaseThrowsException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('either has been deleted (or has never existed)');
        $this->expectExceptionCode(1332446332);

        $this->subject->find(1)->isHidden();
    }

    #[Test]
    public function loadWithModelWithExistingUidLoadsModel(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['title' => 'foo']);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        $model = new TestingModel();
        $model->setUid($uid);

        $this->subject->load($model);

        self::assertTrue(
            $model->isLoaded(),
        );
    }

    #[Test]
    public function loadWithModelWithInexistentUidMarksModelAsDead(): void
    {
        $model = new TestingModel();
        $model->setUid(1);

        $this->subject->load($model);

        self::assertTrue(
            $model->isDead(),
        );
    }

    /////////////////////////////////
    // Tests concerning existsModel
    /////////////////////////////////

    #[Test]
    public function existsModelForUidOfLoadedModelReturnsTrue(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        $this->subject->load($this->subject->find($uid));

        self::assertTrue(
            $this->subject->existsModel($uid),
        );
    }

    #[Test]
    public function existsModelForUidOfNotLoadedModelInDatabaseReturnsTrue(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        self::assertTrue(
            $this->subject->existsModel($uid),
        );
    }

    #[Test]
    public function existsModelForInexistentUidReturnsFalse(): void
    {
        self::assertFalse(
            $this->subject->existsModel(1),
        );
    }

    #[Test]
    public function existsModelForGhostModelWithInexistentUidReturnsFalse(): void
    {
        $uid = 1;
        $this->subject->find($uid);

        self::assertFalse(
            $this->subject->existsModel($uid),
        );
    }

    #[Test]
    public function existsModelForExistingUidLoadsModel(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        $this->subject->existsModel($uid);

        self::assertTrue(
            $this->subject->find($uid)->isLoaded(),
        );
    }

    #[Test]
    public function existsModelForExistentUidOfHiddenRecordReturnsFalse(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['hidden' => 1]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        self::assertFalse(
            $this->subject->existsModel($uid),
        );
    }

    #[Test]
    public function existsModelForExistentUidOfHiddenRecordAndHiddenBeingAllowedReturnsTrue(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['hidden' => 1]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        self::assertTrue(
            $this->subject->existsModel($uid, true),
        );
    }

    #[Test]
    public function existsModelForExistentUidOfLoadedHiddenRecordAndHiddenNotBeingAllowedReturnsFalse(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['hidden' => 1]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        $this->subject->load($this->subject->find($uid));

        self::assertFalse(
            $this->subject->existsModel($uid),
        );
    }

    #[Test]
    public function existsModelForExistentUidOfLoadedHiddenRecordAndHiddenBeingAllowedReturnsTrue(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['hidden' => 1]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        $this->subject->load($this->subject->find($uid));

        self::assertTrue(
            $this->subject->existsModel($uid, true),
        );
    }

    #[Test]
    public function existsModelForExistentUidOfLoadedNonHiddenRecordAndHiddenBeingAllowedReturnsTrue(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['hidden' => 0]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        $this->subject->load($this->subject->find($uid));

        self::assertTrue(
            $this->subject->existsModel($uid, true),
        );
    }

    #[Test]
    public function existsModelForExistentUidOfHiddenAfterLoadingAsNonHiddenAndHiddenBeingAllowedReturnsTrue(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['hidden' => 1]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        $this->subject->load($this->subject->find($uid));

        self::assertTrue(
            $this->subject->existsModel($uid, true),
        );
    }

    ///////////////////////////////////////////
    // Tests concerning getLoadedTestingModel
    ///////////////////////////////////////////

    #[Test]
    public function getLoadedTestingModelReturnsModel(): void
    {
        self::assertInstanceOf(
            AbstractModel::class,
            $this->subject->getLoadedTestingModel([]),
        );
    }

    #[Test]
    public function getLoadedTestingModelReturnsLoadedModel(): void
    {
        self::assertTrue(
            $this->subject->getLoadedTestingModel([])->isLoaded(),
        );
    }

    #[Test]
    public function getLoadedTestingModelReturnsModelWithUid(): void
    {
        self::assertTrue(
            $this->subject->getLoadedTestingModel([])->hasUid(),
        );
    }

    #[Test]
    public function getLoadedTestingModelCreatesRegisteredModel(): void
    {
        $model = $this->subject->getLoadedTestingModel([]);
        $uid = $model->getUid();
        \assert($uid > 0);

        self::assertSame(
            $model,
            $this->subject->find($uid),
        );
    }

    #[Test]
    public function getLoadedTestingModelSetsTheProvidedData(): void
    {
        /** @var TestingModel $model */
        $model = $this->subject->getLoadedTestingModel(
            ['title' => 'foo'],
        );

        self::assertSame(
            'foo',
            $model->getTitle(),
        );
    }

    #[Test]
    public function getLoadedTestingModelCreatesRelations(): void
    {
        $relatedModel = $this->subject->getNewGhost();
        $model = $this->subject->getLoadedTestingModel(
            ['friend' => $relatedModel->getUid()],
        );

        $friend = $model->getFriend();
        self::assertInstanceOf(TestingModel::class, $friend);
        self::assertSame($relatedModel->getUid(), $friend->getUid());
    }

    /////////////////////////////////////////////
    // Tests concerning the foreign key mapping
    /////////////////////////////////////////////

    #[Test]
    public function relatedRecordWithZeroUidIsNull(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertNull(
            $model->getFriend(),
        );
    }

    #[Test]
    public function relatedRecordWithExistingUidReturnsRelatedRecord(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $friendUid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($friendUid > 0);

        $connection->insert('tx_oelib_test', ['friend' => $friendUid]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        $model = $this->subject->find($uid);

        self::assertInstanceOf(TestingModel::class, $model);
        $friend = $model->getFriend();
        self::assertInstanceOf(TestingModel::class, $friend);
        self::assertSame($friendUid, $friend->getUid());
    }

    #[Test]
    public function relatedRecordWithRelationToSelfReturnsSelf(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);
        $connection->update('tx_oelib_test', ['friend' => $uid], ['uid' => $uid]);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);

        self::assertSame(
            $model,
            $model->getFriend(),
        );
    }

    #[Test]
    public function relatedRecordWithExistingUidCanReturnOtherModelType(): void
    {
        $usersConnection = $this->getConnectionPool()->getConnectionForTable('fe_users');
        $usersConnection->insert('fe_users', ['pid' => 0]);

        $ownerUid = (int)$usersConnection->lastInsertId('fe_users');
        \assert($ownerUid > 0);

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['owner' => $ownerUid]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertInstanceOf(
            FrontEndUser::class,
            $model->getOwner(),
        );
    }

    #[Test]
    public function relatedRecordWithExistingUidReturnsRelatedRecordThatCanBeLoaded(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $friendUid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($friendUid > 0);

        $connection->insert('tx_oelib_test', ['friend' => $friendUid]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        $model = $this->subject->find($uid);
        self::assertInstanceOf(TestingModel::class, $model);
        $friend = $model->getFriend();
        self::assertInstanceOf(TestingModel::class, $friend);
        $friend->getTitle();

        self::assertTrue($friend->isLoaded());
    }

    #[Test]
    public function relatedRecordWithInexistentUidReturnsRelatedRecordAsGhost(): void
    {
        $friendUid = 2;
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['friend' => $friendUid]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        $model = $this->subject->find($uid);

        self::assertInstanceOf(TestingModel::class, $model);
        $friend = $model->getFriend();
        self::assertInstanceOf(TestingModel::class, $friend);
        self::assertSame($friendUid, $friend->getUid());
    }

    // Tests concerning the m:n mapping with a comma-separated list of UIDs

    #[Test]
    public function commaSeparatedRelationsWithEmptyStringCreatesEmptyList(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertTrue(
            $model->getChildren()->isEmpty(),
        );
    }

    #[Test]
    public function commaSeparatedRelationsWithOneUidReturnsListWithRelatedModel(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $childUid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($childUid > 0);

        $connection->insert('tx_oelib_test', ['children' => $childUid]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertSame(
            (string)$childUid,
            $model->getChildren()->getUids(),
        );
    }

    #[Test]
    public function commaSeparatedRelationsWithTwoUidsReturnsListWithBothRelatedModels(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $childUid1 = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($childUid1 > 0);
        $connection->insert('tx_oelib_test', ['pid' => 0]);
        $childUid2 = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($childUid2 > 0);

        $connection->insert('tx_oelib_test', ['children' => $childUid1 . ',' . $childUid2]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertSame(
            $childUid1 . ',' . $childUid2,
            $model->getChildren()->getUids(),
        );
    }

    #[Test]
    public function commaSeparatedRelationsWithOneUidAndZeroIgnoresZero(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $childUid1 = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($childUid1 > 0);
        $connection->insert('tx_oelib_test', ['children' => $childUid1 . ',0']);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertSame(
            (string)$childUid1,
            $model->getChildren()->getUids(),
        );
    }

    #[Test]
    public function commaSeparatedRelationHasParentModel(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);

        self::assertSame(
            $model,
            $model->getChildren()->getParentModel(),
        );
    }

    #[Test]
    public function commaSeparatedRelationIsNotOwnedByParent(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);

        self::assertFalse(
            $model->getChildren()->isRelationOwnedByParent(),
        );
    }

    ////////////////////////////////////////////////////////
    // Tests concerning the m:n mapping using an m:n table
    ////////////////////////////////////////////////////////

    #[Test]
    public function mnRelationsWithEmptyStringCreatesEmptyList(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertTrue(
            $model->getRelatedRecords()->isEmpty(),
        );
    }

    #[Test]
    public function mnRelationsWithOneRelatedModelReturnsListWithRelatedModel(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['related_records' => 1]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);
        $connection->insert('tx_oelib_test', ['bidirectional' => 1]);
        $relatedUid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($relatedUid > 0);
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test_article_mm');
        $relationConnection->insert('tx_oelib_test_article_mm', ['uid_local' => $uid, 'uid_foreign' => $relatedUid]);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertSame(
            (string)$relatedUid,
            $model->getRelatedRecords()->getUids(),
        );
    }

    #[Test]
    public function mnRelationsWithTwoRelatedModelsReturnsListWithBothRelatedModels(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['related_records' => 2]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);
        $connection->insert('tx_oelib_test', ['bidirectional' => 1]);
        $relatedUid1 = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($relatedUid1 > 0);
        $connection->insert('tx_oelib_test', ['bidirectional' => 1]);
        $relatedUid2 = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($relatedUid2 > 0);
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test_article_mm');
        $relationConnection->insert('tx_oelib_test_article_mm', ['uid_local' => $uid, 'uid_foreign' => $relatedUid1]);
        $relationConnection->insert('tx_oelib_test_article_mm', ['uid_local' => $uid, 'uid_foreign' => $relatedUid2]);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertSame(
            $relatedUid1 . ',' . $relatedUid2,
            $model->getRelatedRecords()->getUids(),
        );
    }

    #[Test]
    public function mnRelationsReturnsListSortedBySorting(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['related_records' => 2]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);
        $connection->insert('tx_oelib_test', ['bidirectional' => 1]);
        $relatedUid1 = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($relatedUid1 > 0);
        $connection->insert('tx_oelib_test', ['bidirectional' => 1]);
        $relatedUid2 = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($relatedUid2 > 0);
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test_article_mm');
        $relationConnection->insert(
            'tx_oelib_test_article_mm',
            ['uid_local' => $uid, 'uid_foreign' => $relatedUid1, 'sorting' => 2],
        );
        $relationConnection->insert(
            'tx_oelib_test_article_mm',
            ['uid_local' => $uid, 'uid_foreign' => $relatedUid2, 'sorting' => 1],
        );

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertSame(
            $relatedUid2 . ',' . $relatedUid1,
            $model->getRelatedRecords()->getUids(),
        );
    }

    #[Test]
    public function mnRelationHasParentModel(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);

        self::assertSame(
            $model,
            $model->getRelatedRecords()->getParentModel(),
        );
    }

    #[Test]
    public function mnRelationIsNotOwnedByParent(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);

        self::assertFalse(
            $model->getRelatedRecords()->isRelationOwnedByParent(),
        );
    }

    ///////////////////////////////////////////////////////////////////////
    // Tests concerning the bidirectional m:n mapping using an m:n table.
    ///////////////////////////////////////////////////////////////////////

    #[Test]
    public function bidirectionalMNRelationsWithEmptyStringCreatesEmptyList(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertTrue(
            $model->getBidirectional()->isEmpty(),
        );
    }

    #[Test]
    public function bidirectionalMNRelationsWithOneRelatedModelReturnsListWithRelatedModel(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['related_records' => 1]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);
        $connection->insert('tx_oelib_test', ['bidirectional' => 1]);
        $relatedUid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($relatedUid > 0);
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test_article_mm');
        $relationConnection->insert('tx_oelib_test_article_mm', ['uid_local' => $uid, 'uid_foreign' => $relatedUid]);

        /** @var TestingModel $model */
        $model = $this->subject->find($relatedUid);
        self::assertSame(
            (string)$uid,
            $model->getBidirectional()->getUids(),
        );
    }

    #[Test]
    public function bidirectionalMNRelationsWithTwoRelatedModelsReturnsListWithBothRelatedModels(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['related_records' => 1]);

        $uid1 = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid1 > 0);
        $connection->insert('tx_oelib_test', ['related_records' => 1]);
        $uid2 = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid2 > 0);
        $connection->insert('tx_oelib_test', ['bidirectional' => 1]);
        $relatedUid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($relatedUid > 0);
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test_article_mm');
        $relationConnection->insert('tx_oelib_test_article_mm', ['uid_local' => $uid1, 'uid_foreign' => $relatedUid]);
        $relationConnection->insert('tx_oelib_test_article_mm', ['uid_local' => $uid2, 'uid_foreign' => $relatedUid]);

        /** @var TestingModel $model */
        $model = $this->subject->find($relatedUid);
        self::assertSame(
            $uid1 . ',' . $uid2,
            $model->getBidirectional()->getUids(),
        );
    }

    #[Test]
    public function bidirectionalMNRelationsReturnsListSortedByUid(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['related_records' => 1]);

        $uid2 = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid2 > 0);
        $connection->insert('tx_oelib_test', ['related_records' => 1]);
        $uid1 = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid1 > 0);
        $connection->insert('tx_oelib_test', ['bidirectional' => 1]);
        $relatedUid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($relatedUid > 0);
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test_article_mm');
        $relationConnection->insert('tx_oelib_test_article_mm', ['uid_local' => $uid1, 'uid_foreign' => $relatedUid]);
        $relationConnection->insert('tx_oelib_test_article_mm', ['uid_local' => $uid2, 'uid_foreign' => $relatedUid]);

        /** @var TestingModel $model */
        $model = $this->subject->find($relatedUid);
        self::assertSame(
            $uid2 . ',' . $uid1,
            $model->getBidirectional()->getUids(),
        );
    }

    #[Test]
    public function bidirectionalMnRelationHasParentModel(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);

        self::assertSame(
            $model,
            $model->getBidirectional()->getParentModel(),
        );
    }

    #[Test]
    public function bidirectionalMnRelationIsNotOwnedByParent(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);

        self::assertFalse(
            $model->getBidirectional()->isRelationOwnedByParent(),
        );
    }

    ////////////////////////////////////////////////////////////
    // Tests concerning the 1:n mapping using a foreign field.
    ////////////////////////////////////////////////////////////

    #[Test]
    public function oneToManyRelationsWithEmptyStringCreatesEmptyList(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertTrue(
            $model->getComposition()->isEmpty(),
        );
    }

    #[Test]
    public function oneToManyRelationsWithOneRelatedModelReturnsListWithRelatedModel(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['composition' => 1]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_testchild');
        $relationConnection->insert('tx_oelib_testchild', ['parent' => $uid]);

        $relatedUid = (int)$relationConnection->lastInsertId('tx_oelib_testchild');
        \assert($relatedUid > 0);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertSame(
            (string)$relatedUid,
            $model->getComposition()->getUids(),
        );
    }

    #[Test]
    public function oneToManyRelationsCanSortByForeignSortBy(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['composition' => 2]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_testchild');
        $relationConnection->insert('tx_oelib_testchild', ['parent' => $uid, 'title' => 'b']);

        $relatedUid1 = (int)$relationConnection->lastInsertId('tx_oelib_testchild');
        \assert($relatedUid1 > 0);
        $relationConnection->insert('tx_oelib_testchild', ['parent' => $uid, 'title' => 'a']);
        $relatedUid2 = (int)$relationConnection->lastInsertId('tx_oelib_testchild');
        \assert($relatedUid2 > 0);

        $model = $this->subject->find($uid);
        self::assertInstanceOf(TestingModel::class, $model);
        self::assertSame($relatedUid2 . ',' . $relatedUid1, $model->getComposition()->getUids());
    }

    #[Test]
    public function oneToManyRelationsCanSortByForeignDefaultSortBy(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['composition2' => 2]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);
        $connection->insert('tx_oelib_testchild', ['tx_oelib_parent2' => $uid, 'title' => 'b']);
        $relatedUid1 = (int)$connection->lastInsertId('tx_oelib_testchild');
        \assert($relatedUid1 > 0);
        $connection->insert('tx_oelib_testchild', ['tx_oelib_parent2' => $uid, 'title' => 'a']);
        $relatedUid2 = (int)$connection->lastInsertId('tx_oelib_testchild');
        \assert($relatedUid2 > 0);

        $model = $this->subject->find($uid);
        self::assertInstanceOf(TestingModel::class, $model);
        self::assertSame($relatedUid2 . ',' . $relatedUid1, $model->getComposition2()->getUids());
    }

    #[Test]
    public function oneToManyRelationWithoutSortingDoesNotCrash(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['composition_without_sorting' => 1]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_testchild');
        $relationConnection->insert('tx_oelib_testchild', ['tx_oelib_parent3' => $uid]);

        $relatedUid = (int)$relationConnection->lastInsertId('tx_oelib_testchild');
        \assert($relatedUid > 0);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertSame((string)$relatedUid, $model->getCompositionWithoutSorting()->getUids());
    }

    #[Test]
    public function oneToManyRelationsWithOneRelatedModelNotLoadsDeletedModel(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['composition' => 1]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_testchild');
        $relationConnection->insert('tx_oelib_testchild', ['parent' => $uid, 'deleted' => 1]);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);

        self::assertTrue($model->getComposition()->isEmpty());
    }

    #[Test]
    public function oneToManyRelationsWithTwoRelatedModelsReturnsListWithBothRelatedModels(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['composition' => 2]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_testchild');
        $relationConnection->insert('tx_oelib_testchild', ['parent' => $uid, 'title' => 'relation A']);

        $relatedUid1 = (int)$relationConnection->lastInsertId('tx_oelib_testchild');
        \assert($relatedUid1 > 0);
        $relationConnection->insert('tx_oelib_testchild', ['parent' => $uid, 'title' => 'relation B']);
        $relatedUid2 = (int)$relationConnection->lastInsertId('tx_oelib_testchild');
        \assert($relatedUid2 > 0);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertSame(
            $relatedUid1 . ',' . $relatedUid2,
            $model->getComposition()->getUids(),
        );
    }

    #[Test]
    public function oneToManyRelationsReturnsListSortedByForeignSortBy(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['composition' => 2]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_testchild');
        $relationConnection->insert('tx_oelib_testchild', ['parent' => $uid, 'title' => 'relation B']);

        $relatedUid1 = (int)$relationConnection->lastInsertId('tx_oelib_testchild');
        \assert($relatedUid1 > 0);
        $relationConnection->insert('tx_oelib_testchild', ['parent' => $uid, 'title' => 'relation A']);
        $relatedUid2 = (int)$relationConnection->lastInsertId('tx_oelib_testchild');
        \assert($relatedUid2 > 0);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertSame(
            $relatedUid2 . ',' . $relatedUid1,
            $model->getComposition()->getUids(),
        );
    }

    #[Test]
    public function oneToManyRelationHasParentModel(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);

        self::assertSame(
            $model,
            $model->getComposition()->getParentModel(),
        );
    }

    #[Test]
    public function oneToManyRelationIsOwnedByParent(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);

        self::assertTrue(
            $model->getComposition()->isRelationOwnedByParent(),
        );
    }

    // Tests concerning n:1 association mapping

    #[Test]
    public function relatedRecordWithExistingUidReturnsRelatedRecordWithData(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $friendTitle = 'Brianna';
        $connection->insert('tx_oelib_test', ['title' => $friendTitle]);
        $friendUid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($friendUid > 0);
        $connection->insert('tx_oelib_test', ['friend' => $friendUid]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        $model = $this->subject->find($uid);

        self::assertInstanceOf(TestingModel::class, $model);
        $friend = $model->getFriend();
        self::assertInstanceOf(TestingModel::class, $friend);
        self::assertSame($friendTitle, $friend->getTitle());
    }

    // Tests concerning the m:n mapping with a comma-separated list of UIDs

    #[Test]
    public function commaSeparatedRelationsWithOneUidReturnsListWithRelatedModelWithData(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $childTitle = 'Abraham';
        $connection->insert('tx_oelib_test', ['title' => $childTitle]);
        $childUid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($childUid > 0);
        $connection->insert('tx_oelib_test', ['children' => (string)$childUid]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        /** @var TestingModel $firstChild */
        $firstChild = $model->getChildren()->first();
        self::assertSame($childTitle, $firstChild->getTitle());
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function silentlyIgnoresCommaSeparatedOneToManyRelationWithZeroForeignUid(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['children' => '0']);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        // load any property to trigger loading the data
        $model->getTitle();
    }

    // Tests concerning the m:n mapping using an m:n table

    #[Test]
    public function mnRelationsWithOneRelatedModelReturnsListWithRelatedModelWithData(): void
    {
        $relatedTitle = 'Geralt of Rivia';
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['related_records' => 1]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);
        $connection->insert('tx_oelib_test', ['title' => $relatedTitle, 'bidirectional' => 1]);
        $relatedUid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($relatedUid > 0);
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test_article_mm');
        $relationConnection->insert('tx_oelib_test_article_mm', ['uid_local' => $uid, 'uid_foreign' => $relatedUid]);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        /** @var TestingModel $firstRelatedModel */
        $firstRelatedModel = $model->getRelatedRecords()->first();
        self::assertSame($relatedTitle, $firstRelatedModel->getTitle());
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function silentlyIgnoresManyToManyRelationWithZeroForeignUid(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['related_records' => 1]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test_article_mm');
        $relationConnection->insert('tx_oelib_test_article_mm', ['uid_local' => $uid, 'uid_foreign' => 0]);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        // load any property to trigger loading the data
        $model->getTitle();
    }

    // Tests concerning the bidirectional m:n mapping using an m:n table.

    #[Test]
    public function bidirectionalMNRelationsWithOneRelatedModelReturnsListWithRelatedModelWithData(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['related_records' => 1]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);
        $connection->insert('tx_oelib_test', ['bidirectional' => 1]);
        $relatedUid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($relatedUid > 0);
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test_article_mm');
        $relationConnection->insert('tx_oelib_test_article_mm', ['uid_local' => $uid, 'uid_foreign' => $relatedUid]);

        /** @var TestingModel $model */
        $model = $this->subject->find($relatedUid);
        self::assertSame((string)$uid, $model->getBidirectional()->getUids());
    }

    // Tests concerning the 1:n mapping using a foreign field.

    #[Test]
    public function oneToManyRelationsWithOneRelatedModelReturnsListWithRelatedModelWithData(): void
    {
        $relatedTitle = 'Triss Merrigold';
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['composition' => 1]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_testchild');
        $relationConnection->insert('tx_oelib_testchild', ['parent' => $uid, 'title' => $relatedTitle]);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        /** @var TestingModel $firstChildModel */
        $firstChildModel = $model->getComposition()->first();
        self::assertSame($relatedTitle, $firstChildModel->getTitle());
    }

    // Tests concerning findSingleByWhereClause().

    #[Test]
    public function findSingleByWhereClauseWithUidOfInexistentRecordThrowsException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('No records found');
        $this->expectExceptionCode(8074950578);

        $this->subject->findSingleByWhereClause(
            ['uid' => 1],
        );
    }

    #[Test]
    public function findSingleByWhereClauseWithUidOfExistentNotMappedRecordReturnsModelWithTheData(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['title' => 'foo']);

        /** @var TestingModel $model */
        $model = $this->subject->findSingleByWhereClause(['title' => 'foo']);
        self::assertSame(
            'foo',
            $model->getTitle(),
        );
    }

    #[Test]
    public function findSingleByWhereClauseWithUidOfExistentYetMappedRecordReturnsModelWithTheMappedData(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['title' => 'foo']);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);
        /** @var TestingModel $model1 */
        $model1 = $this->subject->find($uid);
        $model1->setTitle('bar');

        /** @var TestingModel $model2 */
        $model2 = $this->subject->findSingleByWhereClause(['title' => 'foo']);
        self::assertSame(
            'bar',
            $model2->getTitle(),
        );
    }

    //////////////////////////////////////////////
    // Tests concerning disabled database access
    //////////////////////////////////////////////
    ////////////////////////////
    // Tests concerning save()
    ////////////////////////////

    #[Test]
    public function saveForGhostDoesNotCommitModelToDatabase(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['title' => 'foo']);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        $this->subject->save($this->subject->find($uid));

        self::assertSame(
            0,
            $connection->count('*', 'tx_oelib_test', [
                'title' => 'foo',
                'tstamp' => GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('date', 'timestamp'),
            ]),
        );
    }

    #[Test]
    public function saveForDeadModelDoesNotCommitDirtyModelToDatabase(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['title' => 'foo']);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');
        $model->markAsDead();

        $this->subject->save($model);

        self::assertSame(
            0,
            $connection->count('*', 'tx_oelib_test', ['title' => 'bar']),
        );
    }

    #[Test]
    public function saveForCleanLoadedModelDoesNotCommitModelToDatabase(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['title' => 'foo']);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');
        $model->markAsClean();

        $this->subject->save($model);

        self::assertSame(
            0,
            $connection->count('*', 'tx_oelib_test', ['title' => 'bar']),
        );
    }

    #[Test]
    public function saveForDirtyLoadedModelWithUidCommitsModelToDatabase(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['title' => 'foo']);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');

        $this->subject->save($model);

        self::assertSame(
            1,
            $connection->count('*', 'tx_oelib_test', ['title' => 'bar']),
        );
    }

    #[Test]
    public function savePersistsStringDataTypes(): void
    {
        $model = new TestingModel();
        $propertyName = 'title';
        $value = 'the title';
        $model->setData([$propertyName => $value]);

        $this->subject->save($model);

        $uid = $model->getUid();

        $result = $this
            ->getConnectionPool()->getConnectionForTable('tx_oelib_test')
            ->select(['*'], 'tx_oelib_test', ['uid' => $uid]);
        $data = $result->fetchAssociative();

        self::assertIsArray($data);
        self::assertArrayHasKey($propertyName, $data);
        self::assertSame($value, $data[$propertyName]);
    }

    /**
     * @return array<string, array{0: non-empty-string, 1: non-empty-string|float}>
     */
    public static function floatDataTypeDataProvider(): array
    {
        return [
            'float as float' => ['float_data', 3.5],
            'float as decimal' => ['decimal_data', 3.5],
            'float as string' => ['string_data', '3.5'],
        ];
    }

    #[Test]
    #[DataProvider('floatDataTypeDataProvider')]
    public function savePersistsFloatDataTypes(string $propertyName, string|float $expectedValue): void
    {
        $model = new TestingModel();
        $model->setData(
            [
                'float_data' => 3.5,
                'decimal_data' => 3.5,
                'string_data' => 3.5,
            ],
        );

        $this->subject->save($model);

        $uid = $model->getUid();

        $result = $this
            ->getConnectionPool()->getConnectionForTable('tx_oelib_test')
            ->select(['*'], 'tx_oelib_test', ['uid' => $uid]);
        $data = $result->fetchAssociative();

        self::assertIsArray($data);
        self::assertArrayHasKey($propertyName, $data);
        $actualValue = $data[$propertyName];
        self::assertTrue(\is_float($actualValue) || \is_string($actualValue));
        self::assertEquals((float)$expectedValue, (float)$actualValue);
    }

    #[Test]
    public function savePersistsIntDataTypes(): void
    {
        $model = new TestingModel();
        $propertyName = 'int_data';
        $model->setData([$propertyName => 42]);

        $this->subject->save($model);

        $uid = $model->getUid();

        $result = $this
            ->getConnectionPool()->getConnectionForTable('tx_oelib_test')
            ->select(['*'], 'tx_oelib_test', ['uid' => $uid]);
        $data = $result->fetchAssociative();

        self::assertIsArray($data);
        self::assertArrayHasKey($propertyName, $data);
        self::assertSame(42, (int)$data[$propertyName]);
    }

    /**
     * @return array<string, array{0: non-empty-string, 1: int}>
     */
    public static function boolDataTypeDataProvider(): array
    {
        return [
            'boolean true' => ['bool_data1', 1],
            'boolean false' => ['bool_data2', 0],
        ];
    }

    #[Test]
    #[DataProvider('boolDataTypeDataProvider')]
    public function savePersistsBoolDataTypes(string $propertyName, int $expectedValue): void
    {
        $model = new TestingModel();
        $model->setData(
            [
                'bool_data1' => true,
                'bool_data2' => false,
            ],
        );

        $this->subject->save($model);

        $uid = $model->getUid();

        $result = $this
            ->getConnectionPool()->getConnectionForTable('tx_oelib_test')
            ->select(['*'], 'tx_oelib_test', ['uid' => $uid]);
        $data = $result->fetchAssociative();

        self::assertIsArray($data);
        self::assertArrayHasKey($propertyName, $data);
        self::assertSame($expectedValue, (int)$data[$propertyName]);
    }

    #[Test]
    public function saveForDirtyLoadedModelWithUidDoesNotChangeTheUid(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['title' => 'foo']);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');

        $this->subject->save($model);

        self::assertSame(
            $uid,
            $model->getUid(),
        );
    }

    #[Test]
    public function saveForDirtyLoadedModelWithUidSetsTimestamp(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['title' => 'foo']);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');

        $this->subject->save($model);

        self::assertSame(
            1,
            $connection->count('*', 'tx_oelib_test', [
                'title' => 'bar',
                'tstamp' => GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('date', 'timestamp'),
            ]),
        );
    }

    #[Test]
    public function saveForDirtyLoadedModelWithUidAndWithoutDataCommitsModelToDatabase(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        $model = new TestingModel();
        $model->setUid($uid);
        $model->setData([]);
        $model->markAsDirty();

        $this->subject->save($model);

        self::assertSame(
            1,
            $connection->count(
                '*',
                'tx_oelib_test',
                ['tstamp' => GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('date', 'timestamp')],
            ),
        );
    }

    #[Test]
    public function saveNewModelFromMemoryAndMapperInTestingModeMarksModelAsDummyModel(): void
    {
        $model = new TestingModel();
        $model->setData(['title' => 'foo']);
        $model->markAsDirty();

        $this->subject->save($model);

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        self::assertSame(
            1,
            $connection->count('*', 'tx_oelib_test', ['title' => 'foo']),
        );
    }

    #[Test]
    public function saveNewModelFromMemoryRegistersModelInMapper(): void
    {
        $model = new TestingModel();
        $model->setData(['title' => 'foo']);
        $model->markAsDirty();

        $this->subject->save($model);
        $uid = $model->getUid();
        \assert($uid > 0);

        self::assertSame(
            $model,
            $this->subject->find($uid),
        );
    }

    #[Test]
    public function isDirtyAfterSaveForDirtyLoadedModelWithUidReturnsFalse(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['title' => 'foo']);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');

        $this->subject->save($model);

        self::assertFalse(
            $this->subject->find($uid)->isDirty(),
        );
    }

    #[Test]
    public function saveForDirtyLoadedModelWithoutUidAndWithoutRelationsCommitsModelToDatabase(): void
    {
        $model = new TestingModel();
        $model->setData(['title' => 'bar']);
        $model->markAsDirty();

        $this->subject->save($model);

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        self::assertSame(
            1,
            $connection->count('*', 'tx_oelib_test', ['title' => 'bar']),
        );
    }

    #[Test]
    public function saveForDirtyLoadedModelWithoutUidAndWithRelationsCommitsModelToDatabase(): void
    {
        $model = new TestingModel();

        $data = ['title' => 'bar'];
        $this->subject->createRelations($data, $model);

        $model->setData($data);
        $model->markAsDirty();

        $this->subject->save($model);

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        self::assertSame(
            1,
            $connection->count('*', 'tx_oelib_test', ['title' => 'bar']),
        );
    }

    #[Test]
    public function saveForDirtyLoadedModelWithoutUidAddsModelToMapAfterSave(): void
    {
        $model = new TestingModel();

        $data = ['title' => 'bar'];
        $this->subject->createRelations($data, $model);

        $model->setData($data);
        $model->markAsDirty();

        $this->subject->save($model);
        $uid = $model->getUid();
        \assert($uid > 0);

        self::assertSame(
            $model,
            $this->subject->find($uid),
        );
    }

    #[Test]
    public function saveForDirtyLoadedModelWithoutUidSetsUidForModel(): void
    {
        $model = new TestingModel();

        $data = ['title' => 'bar'];
        $this->subject->createRelations($data, $model);

        $model->setData($data);
        $model->markAsDirty();

        $this->subject->save($model);

        self::assertTrue(
            $model->hasUid(),
        );
    }

    #[Test]
    public function saveForDirtyLoadedModelWithoutUidSetsUidReceivedFromDatabaseForModel(): void
    {
        $model = new TestingModel();

        $data = ['title' => 'bar'];
        $this->subject->createRelations($data, $model);

        $model->setData($data);
        $model->markAsDirty();

        $this->subject->save($model);

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        self::assertSame(
            1,
            $connection->count('*', 'tx_oelib_test', ['uid' => $model->getUid()]),
        );
    }

    #[Test]
    public function isDirtyAfterSaveForDirtyLoadedModelWithoutUidReturnsFalse(): void
    {
        $model = new TestingModel();

        $data = ['title' => 'bar'];
        $this->subject->createRelations($data, $model);

        $model->setData($data);
        $model->markAsDirty();

        $this->subject->save($model);

        self::assertFalse(
            $model->isDirty(),
        );
    }

    #[Test]
    public function saveForDirtyLoadedModelWithoutUidSetsTimestamp(): void
    {
        $model = new TestingModel();

        $data = ['title' => 'bar'];
        $this->subject->createRelations($data, $model);

        $model->setData($data);
        $model->markAsDirty();

        $this->subject->save($model);

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        self::assertSame(
            1,
            $connection->count('*', 'tx_oelib_test', [
                'title' => 'bar',
                'tstamp' => GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('date', 'timestamp'),
            ]),
        );
    }

    #[Test]
    public function saveForDirtyLoadedModelWithoutUidSetsCreationDate(): void
    {
        $model = new TestingModel();

        $data = ['title' => 'bar'];
        $this->subject->createRelations($data, $model);

        $model->setData($data);
        $model->markAsDirty();

        $this->subject->save($model);

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        self::assertSame(
            1,
            $connection->count('*', 'tx_oelib_test', [
                'title' => 'bar',
                'crdate' => GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('date', 'timestamp'),
            ]),
        );
    }

    #[Test]
    public function saveForDirtyLoadedModelWithNoDataDoesNotCommitModelToDatabase(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['title' => 'foo']);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        self::assertSame(
            0,
            $connection->count('*', 'tx_oelib_test', [
                'title' => 'foo',
                'tstamp' => GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('date', 'timestamp'),
            ]),
        );

        $model = $this->subject->find($uid);
        $model->markAsDirty();

        $this->subject->save($model);

        self::assertSame(
            0,
            $connection->count('*', 'tx_oelib_test', [
                'title' => 'foo',
                'tstamp' => GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('date', 'timestamp'),
            ]),
        );
    }

    #[Test]
    public function isDeadAfterSaveForDirtyLoadedModelWithDeletedFlagSetReturnsTrue(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['title' => 'foo']);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');
        $model->setToDeleted();

        $this->subject->save($model);

        self::assertTrue(
            $this->subject->find($uid)->isDead(),
        );
    }

    #[Test]
    public function saveForModelWithN1RelationSavesUidOfRelatedRecord(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $friendUid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($friendUid > 0);
        $connection->insert('tx_oelib_test', ['friend' => $friendUid]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);
        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');

        $this->subject->save($model);

        self::assertSame(
            1,
            $connection->count('*', 'tx_oelib_test', ['title' => 'bar', 'friend' => $friendUid]),
        );
    }

    #[Test]
    public function saveForModelWithMNCommaSeparatedRelationSavesUidList(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $childUid1 = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($childUid1 > 0);
        $connection->insert('tx_oelib_test', ['pid' => 0]);
        $childUid2 = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($childUid2 > 0);
        $connection->insert('tx_oelib_test', ['children' => $childUid1 . ',' . $childUid2]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);
        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');

        $this->subject->save($model);

        self::assertSame(
            1,
            $connection->count('*', 'tx_oelib_test', ['title' => 'bar', 'children' => $childUid1 . ',' . $childUid2]),
        );
    }

    #[Test]
    public function saveForModelWithMNTableRelationSavesNumberOfRelatedRecords(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['related_records' => 2]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);
        $connection->insert('tx_oelib_test', ['bidirectional' => 1]);
        $relatedUid1 = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($relatedUid1 > 0);
        $connection->insert('tx_oelib_test', ['bidirectional' => 1]);
        $relatedUid2 = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($relatedUid2 > 0);
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test_article_mm');
        $relationConnection->insert('tx_oelib_test_article_mm', ['uid_local' => $uid, 'uid_foreign' => $relatedUid1]);
        $relationConnection->insert('tx_oelib_test_article_mm', ['uid_local' => $uid, 'uid_foreign' => $relatedUid2]);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');

        $this->subject->save($model);

        self::assertSame(
            1,
            $connection->count('*', 'tx_oelib_test', ['title' => 'bar', 'related_records' => 2]),
        );
    }

    #[Test]
    public function saveForModelWithOneToManyRelationSavesNumberOfRelatedRecords(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);
        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');

        $composition = $model->getComposition();
        $mapper = $this->mapperRegistry->getByClassName(TestingChildMapper::class);
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_testchild');
        $relationConnection->insert('tx_oelib_testchild', ['pid' => 0]);

        $childUid1 = (int)$relationConnection->lastInsertId('tx_oelib_testchild');
        \assert($childUid1 > 0);
        $relationConnection->insert('tx_oelib_testchild', ['pid' => 0]);
        $childUid2 = (int)$relationConnection->lastInsertId('tx_oelib_testchild');
        \assert($childUid2 > 0);
        $composition->add($mapper->find($childUid1));
        $composition->add($mapper->find($childUid2));

        $this->subject->save($model);

        self::assertSame(
            1,
            $connection->count('*', 'tx_oelib_test', ['title' => 'bar', 'composition' => 2]),
        );
    }

    #[Test]
    public function saveForModelWithOneToManyRelationSavesDirtyRelatedRecord(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);
        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');

        $composition = $model->getComposition();
        $mapper = $this->mapperRegistry->getByClassName(TestingChildMapper::class);
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_testchild');
        $relationConnection->insert('tx_oelib_testchild', ['pid' => 0]);

        $childUid = (int)$relationConnection->lastInsertId('tx_oelib_testchild');
        \assert($childUid > 0);
        $component = $mapper->find($childUid);
        $composition->add($component);

        $this->subject->save($model);

        self::assertSame(
            1,
            $relationConnection->count(
                '*',
                'tx_oelib_testchild',
                ['uid' => $component->getUid(), 'parent' => $model->getUid()],
            ),
        );
    }

    #[Test]
    public function saveForModelWith1NRelationSavesFirstNewRelatedRecord(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);
        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');

        $component = new TestingChildModel();
        $component->setTitle('foo');
        $model->getComposition()->add($component);

        $this->subject->save($model);

        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_testchild');
        self::assertSame(
            1,
            $relationConnection->count(
                '*',
                'tx_oelib_testchild',
                ['uid' => $component->getUid(), 'parent' => $model->getUid()],
            ),
        );
    }

    #[Test]
    public function saveForModelWith1NRelationSavesSecondNewRelatedRecord(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);
        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');

        $newComponent1 = new TestingChildModel();
        $newComponent1->setTitle('foo');
        $model->getComposition()->add($newComponent1);

        $newComponent2 = new TestingChildModel();
        $newComponent2->setTitle('baz');
        $model->getComposition()->add($newComponent2);

        $this->subject->save($model);

        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_testchild');
        self::assertSame(
            1,
            $relationConnection->count(
                '*',
                'tx_oelib_testchild',
                ['uid' => $newComponent2->getUid(), 'parent' => $model->getUid()],
            ),
        );
    }

    #[Test]
    public function saveForModelWith1NRelationSavesNewRelatedRecordWithPrefixInForeignKey(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);
        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');

        $component = new TestingChildModel();
        $component->setTitle('foo');
        $model->getComposition2()->add($component);

        $this->subject->save($model);

        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_testchild');
        self::assertSame(
            1,
            $relationConnection->count(
                '*',
                'tx_oelib_testchild',
                ['uid' => $component->getUid(), 'tx_oelib_parent2' => $model->getUid()],
            ),
        );
    }

    #[Test]
    public function saveForModelWithOneToManyRelationDeletesUnconnectedRecord(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);
        $model = $this->subject->find($uid);
        $model->markAsDirty();

        $composition = $model->getComposition();
        $mapper = $this->mapperRegistry->getByClassName(TestingChildMapper::class);
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_testchild');
        $relationConnection->insert('tx_oelib_testchild', ['parent' => $model->getUid()]);

        $childUid1 = (int)$relationConnection->lastInsertId('tx_oelib_testchild');
        \assert($childUid1 > 0);
        $component1 = $mapper->find($childUid1);
        $composition->add($component1);
        $relationConnection->insert('tx_oelib_testchild', ['parent' => $model->getUid()]);
        $childUid2 = (int)$relationConnection->lastInsertId('tx_oelib_testchild');
        \assert($childUid2 > 0);
        $component2 = $mapper->find($childUid2);
        self::assertInstanceOf(TestingChildModel::class, $component2);

        $this->subject->save($model);

        // We cannot use `$connection->count()` here because it automatically ignores hidden or deleted records.
        $query = 'SELECT COUNT(*) as count from tx_oelib_testchild WHERE uid = :uid AND deleted = :deleted';
        $queryResult = $relationConnection->executeQuery($query, ['uid' => $component2->getUid(), 'deleted' => 1]);
        $row = $queryResult->fetchAssociative();
        self::assertIsArray($row);
        self::assertSame(1, (int)$row['count']);
    }

    #[Test]
    public function saveForModelWithN1RelationSavesDirtyRelatedRecord(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $friendUid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($friendUid > 0);
        $connection->insert('tx_oelib_test', ['friend' => $friendUid]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);
        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');
        /** @var TestingModel $friend */
        $friend = $this->subject->find($friendUid);
        $friend->setTitle('foo');

        $this->subject->save($model);

        self::assertSame(
            1,
            $connection->count('*', 'tx_oelib_test', ['title' => 'foo', 'uid' => $friendUid]),
        );
    }

    #[Test]
    public function saveForModelWithN1RelationSavesNewRelatedRecord(): void
    {
        $friend = new TestingModel();
        $friend->setTitle('foo');

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);
        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setFriend($friend);

        $this->subject->save($model);

        self::assertSame(
            1,
            $connection->count('*', 'tx_oelib_test', ['uid' => $friend->getUid()]),
        );
    }

    #[Test]
    public function saveForModelWithMNCommaSeparatedRelationSavesDirtyRelatedRecord(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $childUid1 = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($childUid1 > 0);
        $connection->insert('tx_oelib_test', ['pid' => 0]);
        $childUid2 = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($childUid2 > 0);
        $connection->insert('tx_oelib_test', ['children' => $childUid1 . ',' . $childUid2]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);
        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');
        /** @var TestingModel $child */
        $child = $this->subject->find($childUid1);
        $child->setTitle('foo');

        $this->subject->save($model);

        self::assertSame(
            1,
            $connection->count('*', 'tx_oelib_test', ['title' => 'foo', 'uid' => $childUid1]),
        );
    }

    #[Test]
    public function saveAddsModelToCache(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['title' => 'foo']);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');

        $this->subject->save($model);

        $cachedModels = $this->subject->getCachedModels();
        self::assertSame(
            $model->getUid(),
            $cachedModels[0]->getUid(),
        );
    }

    #[Test]
    public function addModelToListMarksParentModelAsDirty(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $parentUid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($parentUid > 0);

        /** @var TestingModel $parent */
        $parent = $this->subject->find($parentUid);
        $child = $this->subject->getNewGhost();

        $parent->getChildren()->add($child);

        self::assertTrue(
            $parent->isDirty(),
        );
    }

    #[Test]
    public function appendListMarksParentModelAsDirty(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $parentUid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($parentUid > 0);

        /** @var TestingModel $parent */
        $parent = $this->subject->find($parentUid);
        $child = $this->subject->getNewGhost();
        /** @var Collection<TestingModel> $list */
        $list = new Collection();
        $list->add($child);

        $parent->getChildren()->append($list);

        self::assertTrue(
            $parent->isDirty(),
        );
    }

    #[Test]
    public function purgeModelFromListMarksModelAsDirty(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $parentUid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($parentUid > 0);

        /** @var TestingModel $parent */
        $parent = $this->subject->find($parentUid);
        $child = $this->subject->getNewGhost();
        $parent->getChildren()->add($child);
        $parent->getChildren()->rewind();

        $parent->getChildren()->purgeCurrent();

        self::assertTrue(
            $parent->isDirty(),
        );
    }

    // Tests concerning save

    #[Test]
    public function saveForModelWithMNTableRelationCreatesIntermediateRelationRecord(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $parentUid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($parentUid > 0);
        $connection->insert('tx_oelib_test', ['pid' => 0]);
        $childUid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($childUid > 0);

        /** @var TestingModel $parent */
        $parent = $this->subject->find($parentUid);
        $child = $this->subject->find($childUid);

        $parent->getRelatedRecords()->add($child);
        $this->subject->save($parent);

        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test_article_mm');
        self::assertSame(
            1,
            $relationConnection->count(
                '*',
                'tx_oelib_test_article_mm',
                ['uid_local' => $parentUid, 'uid_foreign' => $childUid, 'sorting' => 0],
            ),
        );
    }

    #[Test]
    public function saveForModelWithMNTableRelationsCreatesIntermediateRelationRecordAndIncrementsSorting(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $parentUid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($parentUid > 0);
        $connection->insert('tx_oelib_test', ['pid' => 0]);
        $childUid1 = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($childUid1 > 0);
        $connection->insert('tx_oelib_test', ['pid' => 0]);
        $childUid2 = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($childUid2 > 0);

        /** @var TestingModel $parent */
        $parent = $this->subject->find($parentUid);
        $child1 = $this->subject->find($childUid1);
        $child2 = $this->subject->find($childUid2);

        $parent->getRelatedRecords()->add($child1);
        $parent->getRelatedRecords()->add($child2);
        $this->subject->save($parent);

        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test_article_mm');
        self::assertSame(
            1,
            $relationConnection->count(
                '*',
                'tx_oelib_test_article_mm',
                ['uid_local' => $parentUid, 'uid_foreign' => $childUid2, 'sorting' => 1],
            ),
        );
    }

    #[Test]
    public function saveForModelWithBidirectionalMNRelationCreatesIntermediateRelationRecord(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $parentUid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($parentUid > 0);
        $connection->insert('tx_oelib_test', ['pid' => 0]);
        $childUid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($childUid > 0);

        $parent = $this->subject->find($parentUid);
        /** @var TestingModel $child */
        $child = $this->subject->find($childUid);

        $child->getBidirectional()->add($parent);
        $this->subject->save($child);

        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test_article_mm');
        self::assertSame(
            1,
            $relationConnection->count(
                '*',
                'tx_oelib_test_article_mm',
                ['uid_local' => $parentUid, 'uid_foreign' => $childUid, 'sorting' => 0],
            ),
        );
    }

    #[Test]
    public function saveForModelWithBidirectionalMNRelationCreatesIntermediateRelationRecordAndIncrementsSorting(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $parentUid1 = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($parentUid1 > 0);
        $connection->insert('tx_oelib_test', ['pid' => 0]);
        $parentUid2 = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($parentUid2 > 0);
        $connection->insert('tx_oelib_test', ['pid' => 0]);
        $childUid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($childUid > 0);

        $parent1 = $this->subject->find($parentUid1);
        $parent2 = $this->subject->find($parentUid2);
        /** @var TestingModel $child */
        $child = $this->subject->find($childUid);

        $child->getBidirectional()->add($parent1);
        $child->getBidirectional()->add($parent2);
        $this->subject->save($child);

        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test_article_mm');
        self::assertSame(
            1,
            $relationConnection->count(
                '*',
                'tx_oelib_test_article_mm',
                ['uid_local' => $parentUid2, 'uid_foreign' => $childUid, 'sorting' => 1],
            ),
        );
    }

    #[Test]
    public function saveCanSaveFloatDataToFloatColumn(): void
    {
        $model = new TestingModel();
        $model->setData(['float_data' => 9.5]);

        $this->subject->save($model);

        $row = $this->findRecordByUid($model->getUid());
        self::assertSame('9.5', rtrim((string)$row['float_data'], '0'));
    }

    #[Test]
    public function saveCanSaveFloatDataToDecimalColumn(): void
    {
        $model = new TestingModel();
        $model->setData(['decimal_data' => 9.5]);

        $this->subject->save($model);

        $row = $this->findRecordByUid($model->getUid());
        self::assertSame('9.5', rtrim((string)$row['decimal_data'], '0'));
    }

    #[Test]
    public function saveCanSaveFloatDataToStringColumn(): void
    {
        $model = new TestingModel();
        $model->setData(['string_data' => 9.5]);

        $this->subject->save($model);

        $row = $this->findRecordByUid($model->getUid());
        self::assertSame('9.5', rtrim((string)$row['string_data'], '0'));
    }

    /**
     * @return DatabaseRow
     */
    private function findRecordByUid(int $uid): array
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_oelib_test');
        $columns = ['float_data', 'decimal_data', 'string_data'];
        $result = $connection->select($columns, 'tx_oelib_test', ['uid' => $uid]);
        /** @var DatabaseRow|false $data */
        $data = $result->fetchAssociative();
        self::assertIsArray($data);

        return $data;
    }

    /////////////////////////////////////
    // Tests concerning additional keys
    /////////////////////////////////////

    #[Test]
    public function findByKeyFindsLoadedModel(): void
    {
        $model = $this->subject->getLoadedTestingModel(
            ['title' => 'Earl Grey'],
        );

        self::assertSame(
            $model,
            $this->subject->findOneByKeyFromCache('title', 'Earl Grey'),
        );
    }

    #[Test]
    public function findByKeyFindsLastLoadedModelWithSameKey(): void
    {
        $this->subject->getLoadedTestingModel(
            ['title' => 'Earl Grey'],
        );
        $model = $this->subject->getLoadedTestingModel(
            ['title' => 'Earl Grey'],
        );

        self::assertSame(
            $model,
            $this->subject->findOneByKeyFromCache('title', 'Earl Grey'),
        );
    }

    #[Test]
    public function findByKeyFindsSavedModel(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);
        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('Earl Grey');

        $this->subject->save($model);

        self::assertSame(
            $model,
            $this->subject->findOneByKeyFromCache('title', 'Earl Grey'),
        );
    }

    #[Test]
    public function findByKeyFindsLastSavedModelWithSameKey(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $uid1 = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid1 > 0);
        /** @var TestingModel $model1 */
        $model1 = $this->subject->find($uid1);
        $model1->setTitle('Earl Grey');

        $this->subject->save($model1);

        $connection->insert('tx_oelib_test', ['title' => 'Earl Grey']);
        $uid2 = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid2 > 0);
        /** @var TestingModel $model2 */
        $model2 = $this->subject->find($uid2);
        $model2->setTitle('Earl Grey');

        $this->subject->save($model2);

        self::assertSame(
            $model2,
            $this->subject->findOneByKeyFromCache('title', 'Earl Grey'),
        );
    }

    #[Test]
    public function findOneByKeyCanFindModelFromCache(): void
    {
        $model = $this->subject->getLoadedTestingModel(
            ['title' => 'Earl Grey'],
        );

        self::assertSame(
            $model,
            $this->subject->findOneByKey('title', 'Earl Grey'),
        );
    }

    #[Test]
    public function findOneByKeyCanLoadModelFromDatabase(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['title' => 'Earl Grey']);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);

        self::assertSame(
            $uid,
            $this->subject->findOneByKey('title', 'Earl Grey')->getUid(),
        );
    }

    #[Test]
    public function findOneByKeyForInexistentThrowsException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('No records found');
        $this->expectExceptionCode(8074950578);

        $this->subject->findOneByKey('title', 'Darjeeling');
    }

    ////////////////////////////
    // Tests concerning delete
    ////////////////////////////

    #[Test]
    #[DoesNotPerformAssertions]
    public function deleteForDeadModelDoesNotThrowException(): void
    {
        $model = new TestingModel();
        $model->markAsDead();

        $this->subject->delete($model);
    }

    #[Test]
    public function deleteForModelWithoutUidMarksModelAsDead(): void
    {
        $model = new TestingModel();

        $this->subject->delete($model);

        self::assertTrue(
            $model->isDead(),
        );
    }

    #[Test]
    public function deleteForModelWithUidMarksModelAsDead(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);
        $model = $this->subject->find($uid);

        $this->subject->delete($model);

        self::assertTrue(
            $model->isDead(),
        );
    }

    #[Test]
    public function deleteForGhostFromGetNewGhostThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('This model is a memory-only dummy that must not be deleted.');
        $this->expectExceptionCode(1_331_319_817);

        $model = $this->subject->getNewGhost();
        $this->subject->delete($model);
    }

    #[Test]
    public function deleteForModelWithUidWritesModelAsDeletedToDatabase(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);
        $model = $this->subject->find($uid);

        $this->subject->delete($model);

        // We cannot use `$connection->count()` here because it automatically ignores hidden or deleted records.
        $query = 'SELECT COUNT(*) as count from tx_oelib_test WHERE uid = :uid AND deleted = :deleted';
        $queryResult = $connection->executeQuery($query, ['uid' => $uid, 'deleted' => 1]);
        $row = $queryResult->fetchAssociative();
        self::assertIsArray($row);
        self::assertSame(1, (int)$row['count']);
    }

    #[Test]
    public function deleteForModelWithUidStillKeepsModelAccessibleViaDataMapper(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);
        $model = $this->subject->find($uid);

        $this->subject->delete($model);

        self::assertSame(
            $model,
            $this->subject->find($uid),
        );
    }

    #[Test]
    public function deleteForModelWithOneToManyRelationDeletesRelatedElements(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['composition' => 1]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_testchild');
        $relationConnection->insert('tx_oelib_testchild', ['parent' => $uid]);

        $relatedUid = (int)$relationConnection->lastInsertId('tx_oelib_testchild');
        \assert($relatedUid > 0);

        $this->subject->delete($this->subject->find($uid));

        // We cannot use `$connection->count()` here because it automatically ignores hidden or deleted records.
        $query = 'SELECT COUNT(*) as count from tx_oelib_testchild WHERE uid = :uid AND deleted = :deleted';
        $queryResult = $relationConnection->executeQuery($query, ['uid' => $relatedUid, 'deleted' => 1]);
        $row = $queryResult->fetchAssociative();
        self::assertIsArray($row);
        self::assertSame(1, (int)$row['count']);
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function deleteForDirtyModelWithOneToManyRelationToDirtyElementDoesNotCrash(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['composition' => 1]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_testchild');
        $relationConnection->insert('tx_oelib_testchild', ['parent' => $uid]);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        /** @var TestingModel $relatedModel */
        $relatedModel = $model->getComposition()->first();

        $model->setTitle('foo');
        $relatedModel->setTitle('bar');

        $this->subject->delete($model);
    }

    ///////////////////////////////////////
    // Tests concerning findAllByRelation
    ///////////////////////////////////////

    #[Test]
    public function findAllByRelationWithEmptyKeyThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$relationKey must not be empty');
        $this->expectExceptionCode(1_331_319_921);

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);
        /** @var TestingModel $model */
        $model = $this->subject->find($uid);

        // @phpstan-ignore-next-line We are explicitly testing for a contract violation here.
        $this->mapperRegistry->getByClassName(TestingChildMapper::class)->findAllByRelation($model, '');
    }

    #[Test]
    public function findAllByRelationForNoMatchesReturnsEmptyList(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);
        $model = $this->subject->find($uid);

        $mapper = $this->mapperRegistry->getByClassName(TestingChildMapper::class);
        self::assertTrue(
            $mapper->findAllByRelation($model, 'parent')->isEmpty(),
        );
    }

    #[Test]
    public function findAllByRelationNotReturnsNotMatchingRecords(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $uid1 = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid1 > 0);
        $model = $this->subject->find($uid1);
        $connection->insert('tx_oelib_test', ['pid' => 0]);
        $uid2 = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid2 > 0);
        $anotherModel = $this->subject->find($uid2);
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_testchild');
        $relationConnection->insert('tx_oelib_testchild', ['parent' => $anotherModel->getUid()]);

        $mapper = $this->mapperRegistry->getByClassName(TestingChildMapper::class);
        self::assertTrue(
            $mapper->findAllByRelation($model, 'parent')->isEmpty(),
        );
    }

    #[Test]
    public function findAllByRelationCanReturnOneMatch(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);
        $model = $this->subject->find($uid);
        $mapper = $this->mapperRegistry->getByClassName(TestingChildMapper::class);
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_testchild');
        $relationConnection->insert('tx_oelib_testchild', ['parent' => $model->getUid()]);

        $relatedUid = (int)$relationConnection->lastInsertId('tx_oelib_testchild');
        \assert($relatedUid > 0);
        $relatedModel = $mapper->find($relatedUid);

        $result = $mapper->findAllByRelation($model, 'parent');
        self::assertCount(1, $result);
        self::assertSame(
            $relatedModel,
            $result->first(),
        );
    }

    #[Test]
    public function findAllByRelationCanReturnTwoMatches(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($uid > 0);
        $model = $this->subject->find($uid);
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_testchild');
        $relationConnection->insert('tx_oelib_testchild', ['parent' => $model->getUid()]);
        $relationConnection->insert('tx_oelib_testchild', ['parent' => $model->getUid()]);

        $result = $this->mapperRegistry->getByClassName(TestingChildMapper::class)->findAllByRelation($model, 'parent');

        self::assertCount(2, $result);
    }

    #[Test]
    public function findAllByRelationIgnoresIgnoreList(): void
    {
        $childMapper = $this->mapperRegistry->getByClassName(TestingChildMapper::class);
        $parentMapper = $this->mapperRegistry->getByClassName(TestingMapper::class);

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 0]);

        $parentUid = (int)$connection->lastInsertId('tx_oelib_test');
        \assert($parentUid > 0);
        $parentModel = $parentMapper->find($parentUid);

        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_testchild');
        $relationConnection->insert('tx_oelib_testchild', ['parent' => $parentModel->getUid()]);

        $childUid1 = (int)$relationConnection->lastInsertId('tx_oelib_testchild');
        \assert($childUid1 > 0);
        $relatedModel = $childMapper->find($childUid1);
        $relationConnection->insert('tx_oelib_testchild', ['parent' => $parentModel->getUid()]);
        $childUid2 = (int)$relationConnection->lastInsertId('tx_oelib_testchild');
        \assert($childUid2 > 0);
        $ignoredRelatedModel = $childMapper->find($childUid2);

        /** @var Collection<TestingChildModel> $ignoreList */
        $ignoreList = new Collection();
        $ignoreList->add($ignoredRelatedModel);

        $result = $childMapper->findAllByRelation($parentModel, 'parent', $ignoreList);

        self::assertCount(1, $result);
        self::assertSame($relatedModel, $result->first());
    }
}
