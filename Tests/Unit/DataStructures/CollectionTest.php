<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\DataStructures;

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Tests\Unit\Model\Fixtures\TestingModel;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Oelib\DataStructures\Collection
 */
final class CollectionTest extends UnitTestCase
{
    /**
     * @var Collection<TestingModel>
     */
    private Collection $subject;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var Collection<TestingModel> $subject */
        $subject = new Collection();
        $this->subject = $subject;
    }

    /**
     * @return -1|0|1
     */
    public function sortByTitleAscending(TestingModel $firstModel, TestingModel $secondModel): int
    {
        return strcmp($firstModel->getTitle(), $secondModel->getTitle());
    }

    /**
     * @return -1|0|1
     */
    public function sortByTitleDescending(TestingModel $firstModel, TestingModel $secondModel): int
    {
        return strcmp($secondModel->getTitle(), $firstModel->getTitle());
    }

    /**
     * Adds models with the given titles to the subject, one for each title
     * given in $titles.
     *
     * @param array<string> $titles the titles for the models, must not be empty
     */
    private function addModelsToFixture(array $titles = ['']): void
    {
        foreach ($titles as $title) {
            $model = new TestingModel();
            $model->setTitle($title);
            $this->subject->add($model);
        }
    }

    #[Test]
    public function sortByTitleAscendingForFirstModelTitleAlphaAndSecondModelTitleBetaReturnsMinusOne(): void
    {
        $firstModel = new TestingModel();
        $firstModel->setTitle('alpha');

        $secondModel = new TestingModel();
        $secondModel->setTitle('beta');

        self::assertSame(
            -1,
            $this->sortByTitleAscending($firstModel, $secondModel),
        );
    }

    #[Test]
    public function sortByTitleAscendingForFirstModelTitleBetaAndSecondModelTitleAlphaReturnsOne(): void
    {
        $firstModel = new TestingModel();
        $firstModel->setTitle('beta');

        $secondModel = new TestingModel();
        $secondModel->setTitle('alpha');

        self::assertSame(
            1,
            $this->sortByTitleAscending($firstModel, $secondModel),
        );
    }

    #[Test]
    public function sortByTitleAscendingForFirstAndSecondModelTitleSameReturnsZero(): void
    {
        $firstModel = new TestingModel();
        $firstModel->setTitle('alpha');

        $secondModel = new TestingModel();
        $secondModel->setTitle('alpha');

        self::assertSame(
            0,
            $this->sortByTitleAscending($firstModel, $secondModel),
        );
    }

    ///////////////////////////////////////////
    // Tests concerning sortByTitleDescending
    ///////////////////////////////////////////

    #[Test]
    public function sortByTitleDescendingForFirstModelTitleAlphaAndSecondModelTitleBetaReturnsOne(): void
    {
        $firstModel = new TestingModel();
        $firstModel->setTitle('alpha');

        $secondModel = new TestingModel();
        $secondModel->setTitle('beta');

        self::assertSame(
            1,
            $this->sortByTitleDescending($firstModel, $secondModel),
        );
    }

    #[Test]
    public function sortByTitleDescendingForFirstModelTitleBetaAndSecondModelTitleAlphaReturnsMinusOne(): void
    {
        $firstModel = new TestingModel();
        $firstModel->setTitle('beta');

        $secondModel = new TestingModel();
        $secondModel->setTitle('alpha');

        self::assertSame(
            -1,
            $this->sortByTitleDescending($firstModel, $secondModel),
        );
    }

    #[Test]
    public function sortByTitleDescendingForFirstAndSecondModelTitleSameReturnsZero(): void
    {
        $firstModel = new TestingModel();
        $firstModel->setTitle('alpha');

        $secondModel = new TestingModel();
        $secondModel->setTitle('alpha');

        self::assertSame(
            0,
            $this->sortByTitleDescending($firstModel, $secondModel),
        );
    }

    #[Test]
    public function addModelsToFixtureForOneGivenTitleAddsOneModelToFixture(): void
    {
        $this->addModelsToFixture(['foo']);

        self::assertCount(1, $this->subject);
    }

    #[Test]
    public function addModelsToFixtureForOneGivenTitleAddsModelWithTitleGiven(): void
    {
        $this->addModelsToFixture(['foo']);

        /** @var TestingModel $firstItem */
        $firstItem = $this->subject->first();
        self::assertSame(
            'foo',
            $firstItem->getTitle(),
        );
    }

    #[Test]
    public function addModelsToFixtureForTwoGivenTitlesAddsTwoModelsToFixture(): void
    {
        $this->addModelsToFixture(['foo', 'bar']);

        self::assertCount(2, $this->subject);
    }

    #[Test]
    public function addModelsToFixtureForTwoGivenTitlesAddsFirstTitleToFirstModelFixture(): void
    {
        $this->addModelsToFixture(['bar', 'foo']);

        /** @var TestingModel $firstItem */
        $firstItem = $this->subject->first();
        self::assertSame(
            'bar',
            $firstItem->getTitle(),
        );
    }

    #[Test]
    public function addModelsToFixtureForThreeGivenTitlesAddsThreeModelsToFixture(): void
    {
        $this->addModelsToFixture(['foo', 'bar', 'fooBar']);

        self::assertCount(3, $this->subject);
    }

    #[Test]
    public function isEmptyForEmptyListReturnsTrue(): void
    {
        self::assertTrue(
            $this->subject->isEmpty(),
        );
    }

    #[Test]
    public function isEmptyAfterAddingModelReturnsFalse(): void
    {
        $this->addModelsToFixture();

        self::assertFalse(
            $this->subject->isEmpty(),
        );
    }

    #[Test]
    public function countForEmptyListReturnsZero(): void
    {
        self::assertCount(0, $this->subject);
    }

    #[Test]
    public function countWithOneModelWithoutUidReturnsOne(): void
    {
        $this->addModelsToFixture();

        self::assertCount(1, $this->subject);
    }

    #[Test]
    public function countWithOneModelWithUidReturnsOne(): void
    {
        $model = new TestingModel();
        $model->setUid(1);

        $this->subject->add($model);

        self::assertCount(1, $this->subject);
    }

    #[Test]
    public function countWithTwoDifferentModelsReturnsTwo(): void
    {
        $this->addModelsToFixture(['', '']);

        self::assertCount(2, $this->subject);
    }

    #[Test]
    public function countAfterAddingTheSameModelTwiceReturnsOne(): void
    {
        $model = new TestingModel();
        $this->subject->add($model);
        $this->subject->add($model);

        self::assertCount(1, $this->subject);
    }

    #[Test]
    public function currentWithOneItemReturnsThatItem(): void
    {
        $model = new TestingModel();
        $this->subject->add($model);

        self::assertSame(
            $model,
            $this->subject->current(),
        );
    }

    #[Test]
    public function currentWithTwoItemsInitiallyReturnsTheFirstItem(): void
    {
        $model1 = new TestingModel();
        $this->subject->add($model1);
        $model2 = new TestingModel();
        $this->subject->add($model2);

        self::assertSame(
            $model1,
            $this->subject->current(),
        );
    }

    #[Test]
    public function keyInitiallyReturnsZero(): void
    {
        self::assertSame(
            0,
            $this->subject->key(),
        );
    }

    #[Test]
    public function keyAfterNextInListWithOneElementReturnsOne(): void
    {
        $this->addModelsToFixture();
        $this->subject->next();

        self::assertSame(
            1,
            $this->subject->key(),
        );
    }

    #[Test]
    public function currentWithTwoItemsAfterNextReturnsTheSecondItem(): void
    {
        $model1 = new TestingModel();
        $this->subject->add($model1);
        $model2 = new TestingModel();
        $this->subject->add($model2);

        $this->subject->next();

        self::assertSame(
            $model2,
            $this->subject->current(),
        );
    }

    #[Test]
    public function rewindAfterNextResetsKeyToZero(): void
    {
        $this->subject->next();
        $this->subject->rewind();

        self::assertSame(
            0,
            $this->subject->key(),
        );
    }

    #[Test]
    public function rewindAfterNextForOneItemsResetsCurrentToTheOnlyItem(): void
    {
        $model = new TestingModel();
        $this->subject->add($model);

        $this->subject->next();
        $this->subject->rewind();

        self::assertSame(
            $model,
            $this->subject->current(),
        );
    }

    #[Test]
    public function firstForEmptyListReturnsNull(): void
    {
        self::assertNull(
            $this->subject->first(),
        );
    }

    #[Test]
    public function firstForListWithOneItemReturnsThatItem(): void
    {
        $model = new TestingModel();
        $this->subject->add($model);

        self::assertSame(
            $model,
            $this->subject->first(),
        );
    }

    #[Test]
    public function firstWithTwoItemsReturnsTheFirstItem(): void
    {
        $model1 = new TestingModel();
        $this->subject->add($model1);
        $model2 = new TestingModel();
        $this->subject->add($model2);

        self::assertSame(
            $model1,
            $this->subject->first(),
        );
    }

    #[Test]
    public function firstWithTwoItemsAfterNextReturnsTheFirstItem(): void
    {
        $model1 = new TestingModel();
        $this->subject->add($model1);
        $model2 = new TestingModel();
        $this->subject->add($model2);

        $this->subject->next();

        self::assertSame(
            $model1,
            $this->subject->first(),
        );
    }

    #[Test]
    public function validForEmptyListReturnsFalse(): void
    {
        self::assertFalse(
            $this->subject->valid(),
        );
    }

    #[Test]
    public function validForOneElementInitiallyReturnsTrue(): void
    {
        $this->addModelsToFixture();

        self::assertTrue(
            $this->subject->valid(),
        );
    }

    #[Test]
    public function validForOneElementAfterNextReturnsFalse(): void
    {
        $this->addModelsToFixture();

        $this->subject->next();

        self::assertFalse(
            $this->subject->valid(),
        );
    }

    #[Test]
    public function validForOneElementAfterNextAndRewindReturnsTrue(): void
    {
        $this->addModelsToFixture();

        $this->subject->next();
        $this->subject->rewind();

        self::assertTrue(
            $this->subject->valid(),
        );
    }

    #[Test]
    public function isIterator(): void
    {
        self::assertInstanceOf(\Iterator::class, $this->subject);
    }

    #[Test]
    public function getUidsForEmptyListReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getUids(),
        );
    }

    #[Test]
    public function getUidsForOneItemsWithoutUidReturnsEmptyString(): void
    {
        $this->addModelsToFixture();

        self::assertSame(
            '',
            $this->subject->getUids(),
        );
    }

    #[Test]
    public function getUidsForOneItemsWithUidReturnsThatUid(): void
    {
        $model = new TestingModel();
        $model->setUid(1);

        $this->subject->add($model);

        self::assertSame(
            '1',
            $this->subject->getUids(),
        );
    }

    #[Test]
    public function getUidsForTwoItemsWithUidReturnsCommaSeparatedItems(): void
    {
        $model1 = new TestingModel();
        $model1->setUid(1);

        $this->subject->add($model1);
        $model2 = new TestingModel();
        $model2->setUid(42);

        $this->subject->add($model2);

        self::assertSame(
            '1,42',
            $this->subject->getUids(),
        );
    }

    #[Test]
    public function getUidsForTwoItemsWithDecreasingUidReturnsItemsInOrdnerOfInsertion(): void
    {
        $model1 = new TestingModel();
        $model1->setUid(42);

        $this->subject->add($model1);
        $model2 = new TestingModel();
        $model2->setUid(1);

        $this->subject->add($model2);

        self::assertSame(
            '42,1',
            $this->subject->getUids(),
        );
    }

    #[Test]
    public function getUidsForDuplicateUidsReturnsUidsInOrdnerOfFirstInsertion(): void
    {
        $model1 = new TestingModel();
        $model1->setUid(1);

        $this->subject->add($model1);
        $model2 = new TestingModel();
        $model2->setUid(2);

        $this->subject->add($model2);

        $this->subject->add($model1);

        self::assertSame(
            '1,2',
            $this->subject->getUids(),
        );
    }

    #[Test]
    public function getUidsForElementThatGotItsUidAfterAddingItReturnsItsUid(): void
    {
        $model = new TestingModel();
        $this->subject->add($model);
        $model->setUid(42);

        self::assertSame(
            '42',
            $this->subject->getUids(),
        );
    }

    #[Test]
    public function hasUidForInexistentUidReturnsFalse(): void
    {
        self::assertFalse(
            $this->subject->hasUid(42),
        );
    }

    #[Test]
    public function hasUidForExistingUidReturnsTrue(): void
    {
        $model = new TestingModel();
        $model->setUid(42);

        $this->subject->add($model);

        self::assertTrue(
            $this->subject->hasUid(42),
        );
    }

    #[Test]
    public function hasUidForElementThatGotItsUidAfterAddingItReturnsTrue(): void
    {
        $model = new TestingModel();
        $this->subject->add($model);
        $model->setUid(42);

        self::assertTrue(
            $this->subject->hasUid(42),
        );
    }

    #[Test]
    public function sortWithTwoModelsAndSortByTitleAscendingFunctionSortsModelsByTitleAscending(): void
    {
        $this->addModelsToFixture(['Beta', 'Alpha']);
        $this->subject->sort(fn(
            TestingModel $firstModel,
            TestingModel $secondModel,
        ): int
            => $this->sortByTitleAscending($firstModel, $secondModel));

        /** @var TestingModel $firstItem */
        $firstItem = $this->subject->first();
        self::assertSame(
            'Alpha',
            $firstItem->getTitle(),
        );
    }

    #[Test]
    public function sortWithThreeModelsAndSortByTitleAscendingFunctionSortsModelsByTitleAscending(): void
    {
        $this->addModelsToFixture(['Zeta', 'Beta', 'Alpha']);
        $this->subject->sort(fn(
            TestingModel $firstModel,
            TestingModel $secondModel,
        ): int
            => $this->sortByTitleAscending($firstModel, $secondModel));

        /** @var TestingModel $firstItem */
        $firstItem = $this->subject->first();
        self::assertSame(
            'Alpha',
            $firstItem->getTitle(),
        );
    }

    #[Test]
    public function sortWithTwoModelsAndSortByTitleDescendingFunctionSortsModelsByTitleDescending(): void
    {
        $this->addModelsToFixture(['Alpha', 'Beta']);
        $this->subject->sort(fn(
            TestingModel $firstModel,
            TestingModel $secondModel,
        ): int
            => $this->sortByTitleDescending($firstModel, $secondModel));

        /** @var TestingModel $firstItem */
        $firstItem = $this->subject->first();
        self::assertSame(
            'Beta',
            $firstItem->getTitle(),
        );
    }

    #[Test]
    public function sortMakesListDirty(): void
    {
        $subject = $this->createPartialMock(Collection::class, ['markAsDirty']);
        $subject->expects(self::once())->method('markAsDirty');

        $subject->sort(fn(
            TestingModel $firstModel,
            TestingModel $secondModel,
        ): int
            => $this->sortByTitleAscending($firstModel, $secondModel));
    }

    #[Test]
    public function appendEmptyListToEmptyListMakesEmptyList(): void
    {
        /** @var Collection<TestingModel> $otherList */
        $otherList = new Collection();
        $this->subject->append($otherList);

        self::assertTrue(
            $this->subject->isEmpty(),
        );
    }

    #[Test]
    public function appendTwoItemListToEmptyListMakesTwoItemList(): void
    {
        /** @var Collection<TestingModel> $otherList */
        $otherList = new Collection();
        $model1 = new TestingModel();
        $otherList->add($model1);
        $model2 = new TestingModel();
        $otherList->add($model2);

        $this->subject->append($otherList);

        self::assertCount(2, $this->subject);
    }

    #[Test]
    public function appendEmptyListToTwoItemListMakesTwoItemList(): void
    {
        $this->addModelsToFixture(['First', 'Second']);

        /** @var Collection<TestingModel> $otherList */
        $otherList = new Collection();
        $this->subject->append($otherList);

        self::assertCount(2, $this->subject);
    }

    #[Test]
    public function appendOneItemListToOneItemListWithTheSameItemMakesOneItemList(): void
    {
        $model = new TestingModel();
        $model->setUid(42);

        $this->subject->add($model);

        /** @var Collection<TestingModel> $otherList */
        $otherList = new Collection();
        $otherList->add($model);

        $this->subject->append($otherList);

        self::assertCount(1, $this->subject);
    }

    #[Test]
    public function appendTwoItemListKeepsOrderOfAppendedItems(): void
    {
        /** @var Collection<TestingModel> $otherList */
        $otherList = new Collection();
        $model1 = new TestingModel();
        $otherList->add($model1);
        $model2 = new TestingModel();
        $otherList->add($model2);

        $this->subject->append($otherList);

        self::assertSame(
            $model1,
            $this->subject->first(),
        );
    }

    #[Test]
    public function appendAppendsItemAfterExistingItems(): void
    {
        $model = new TestingModel();
        $this->subject->add($model);

        /** @var Collection<TestingModel> $otherList */
        $otherList = new Collection();
        $otherModel = new TestingModel();
        $otherList->add($otherModel);

        $this->subject->append($otherList);

        self::assertSame(
            $model,
            $this->subject->first(),
        );
    }

    #[Test]
    public function purgeCurrentWithEmptyListDoesNotFail(): void
    {
        $this->subject->purgeCurrent();
    }

    #[Test]
    public function purgeCurrentWithRewoundOneElementListMakesListEmpty(): void
    {
        $this->addModelsToFixture();

        $this->subject->rewind();
        $this->subject->purgeCurrent();

        self::assertTrue(
            $this->subject->isEmpty(),
        );
    }

    #[Test]
    public function purgeCurrentWithRewoundOneElementListMakesPointerInvalid(): void
    {
        $this->addModelsToFixture();

        $this->subject->rewind();
        $this->subject->purgeCurrent();

        self::assertFalse(
            $this->subject->valid(),
        );
    }

    #[Test]
    public function purgeCurrentWithOneElementListAndPointerAfterLastItemLeavesListUntouched(): void
    {
        $this->addModelsToFixture();

        $this->subject->rewind();
        $this->subject->next();
        $this->subject->purgeCurrent();

        self::assertFalse(
            $this->subject->isEmpty(),
        );
    }

    #[Test]
    public function purgeCurrentForFirstOfTwoElementsMakesOneItemList(): void
    {
        $this->addModelsToFixture(['', '']);

        $this->subject->rewind();
        $this->subject->purgeCurrent();

        self::assertCount(1, $this->subject);
    }

    #[Test]
    public function purgeCurrentForSecondOfTwoElementsMakesOneItemList(): void
    {
        $this->addModelsToFixture(['', '']);

        $this->subject->rewind();
        $this->subject->next();
        $this->subject->purgeCurrent();

        self::assertCount(1, $this->subject);
    }

    #[Test]
    public function purgeCurrentForFirstOfTwoElementsSetsPointerToFormerSecondElement(): void
    {
        $this->addModelsToFixture();

        $model = new TestingModel();
        $this->subject->add($model);

        $this->subject->rewind();
        $this->subject->purgeCurrent();

        self::assertSame(
            $model,
            $this->subject->current(),
        );
    }

    #[Test]
    public function purgeCurrentForSecondOfTwoElementsInWhileLoopDoesNotChangeNumberOfIterations(): void
    {
        $this->addModelsToFixture(['', '']);

        $completedIterations = 0;

        while ($this->subject->valid()) {
            if ($completedIterations === 1) {
                $this->subject->purgeCurrent();
            }

            ++$completedIterations;
            $this->subject->next();
        }

        self::assertSame(
            2,
            $completedIterations,
        );
    }

    #[Test]
    public function purgeCurrentForModelWithUidRemovesModelFromGetUids(): void
    {
        $model = new TestingModel();
        $model->setUid(1);

        $this->subject->add($model);

        $this->subject->rewind();
        $this->subject->purgeCurrent();

        self::assertSame(
            '',
            $this->subject->getUids(),
        );
    }

    #[Test]
    public function toArrayForNoElementsReturnsEmptyArray(): void
    {
        self::assertSame(
            [],
            $this->subject->toArray(),
        );
    }

    #[Test]
    public function toArrayWithOneElementReturnsArrayWithElement(): void
    {
        $model = new TestingModel();
        $this->subject->add($model);

        self::assertSame(
            [$model],
            $this->subject->toArray(),
        );
    }

    #[Test]
    public function toArrayWithTwoElementsReturnsArrayWithBothElementsInAddingOrder(): void
    {
        $model1 = new TestingModel();
        $this->subject->add($model1);
        $model2 = new TestingModel();
        $this->subject->add($model2);

        self::assertSame(
            [$model1, $model2],
            $this->subject->toArray(),
        );
    }

    #[Test]
    public function parentModelByDefaultIsNull(): void
    {
        self::assertNull($this->subject->getParentModel());
    }

    #[Test]
    public function setParentModelSetsParentModel(): void
    {
        $model = new TestingModel();
        $this->subject->setParentModel($model);

        self::assertSame(
            $model,
            $this->subject->getParentModel(),
        );
    }

    #[Test]
    public function addWithoutParentModelIsNoProblem(): void
    {
        $model = new TestingModel();
        $this->subject->add($model);
    }

    #[Test]
    public function addWithoutParentModelMarksParentModelAsDirty(): void
    {
        $parentModel = new TestingModel();
        self::assertFalse($parentModel->isDirty());
        $this->subject->setParentModel($parentModel);

        $model = new TestingModel();
        $this->subject->add($model);

        self::assertTrue($parentModel->isDirty());
    }

    #[Test]
    public function isRelationOwnedByParentByDefaultIsFalse(): void
    {
        self::assertFalse($this->subject->isRelationOwnedByParent());
    }

    #[Test]
    public function isRelationOwnedByParentCanBeSetToTrue(): void
    {
        $this->subject->markAsOwnedByParent();

        self::assertTrue($this->subject->isRelationOwnedByParent());
    }
}
