<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Model\Fixtures;

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Model\AbstractModel;
use OliverKlee\Oelib\Model\FrontEndUser;

/**
 * Testing model.
 */
class TestingModel extends AbstractModel
{
    public function getTitle(): string
    {
        return $this->getAsString('title');
    }

    public function setTitle(string $value): void
    {
        $this->setAsString('title', $value);
    }

    public function setHeader(string $value): void
    {
        $this->setAsString('header', $value);
    }

    /**
     * Sets the deleted property via set().
     *
     * Note: This function is expected to fail.
     */
    public function setDeletedPropertyUsingSet(): void
    {
        $this->setAsBoolean('deleted', true);
    }

    /**
     * Gets the "friend" data item. This is an n:1 relation.
     */
    public function getFriend(): ?TestingModel
    {
        /** @var TestingModel|null $model */
        $model = $this->getAsModel('friend');

        return $model;
    }

    /**
     * Sets the "friend" data item. This is an n:1 relation.
     */
    public function setFriend(TestingModel $friend): void
    {
        $this->set('friend', $friend);
    }

    /**
     * Gets the "owner" data item. This is an n:1 relation.
     */
    public function getOwner(): FrontEndUser
    {
        /** @var FrontEndUser $model */
        $model = $this->getAsModel('owner');

        return $model;
    }

    /**
     * Gets the "children" data item. This is a 1:n relation.
     *
     * @return Collection<TestingModel>
     */
    public function getChildren(): Collection
    {
        /** @var Collection<TestingModel> $models */
        $models = $this->getAsCollection('children');

        return $models;
    }

    /**
     * Gets the "related_records" data item. This is an m:n relation.
     *
     * @return Collection<TestingModel>
     */
    public function getRelatedRecords(): Collection
    {
        /** @var Collection<TestingModel> $models */
        $models = $this->getAsCollection('related_records');

        return $models;
    }

    /**
     * Adds a related record.
     */
    public function addRelatedRecord(TestingModel $record): void
    {
        $this->getRelatedRecords()->add($record);
    }

    /**
     * Gets the "bidirectional" data item. This is an m:n relation.
     *
     * @return Collection<TestingModel>
     */
    public function getBidirectional(): Collection
    {
        /** @var Collection<TestingModel> $models */
        $models = $this->getAsCollection('bidirectional');

        return $models;
    }

    /**
     * Gets the "composition" data item. This is an 1:n relation.
     *
     * @return Collection<TestingChildModel>
     */
    public function getComposition(): Collection
    {
        /** @var Collection<TestingChildModel> $models */
        $models = $this->getAsCollection('composition');

        return $models;
    }

    /**
     * Adds $model to the "composition" relation.
     */
    public function addCompositionRecord(TestingChildModel $model): void
    {
        $this->getComposition()->add($model);
    }

    /**
     * Gets the "composition2" data item. This is an 1:n relation.
     *
     * @return Collection<TestingChildModel>
     */
    public function getComposition2(): Collection
    {
        /** @var Collection<TestingChildModel> $association */
        $association = $this->getAsCollection('composition2');

        return $association;
    }

    /**
     * Gets the "composition2" data item. This is an 1:n relation without sorting.
     *
     * @return Collection<TestingChildModel>
     */
    public function getCompositionWithoutSorting(): Collection
    {
        /** @var Collection<TestingChildModel> $association */
        $association = $this->getAsCollection('composition_without_sorting');

        return $association;
    }

    /**
     * Gets the data from the "float_data" column.
     *
     * @return float the data from the "float_data" column
     */
    public function getFloatFromFloatData(): float
    {
        return $this->getAsFloat('float_data');
    }

    /**
     * Gets the data from the "decimal_data" column.
     *
     * @return float the data from the "decimal_data" column
     */
    public function getFloatFromDecimalData(): float
    {
        return $this->getAsFloat('decimal_data');
    }

    /**
     * Gets the data from the "string_data" column.
     *
     * @return float the data from the "string_data" column
     */
    public function getFloatFromStringData(): float
    {
        return $this->getAsFloat('string_data');
    }

    /**
     * @param non-empty-string $key
     */
    public function existsKey(string $key): bool
    {
        return parent::existsKey($key);
    }

    /**
     * @throws \UnexpectedValueException
     */
    public function getAsModel(string $key): ?AbstractModel
    {
        return parent::getAsModel($key);
    }

    /**
     * @param non-empty-string $key
     */
    public function getAsBoolean(string $key): bool
    {
        return parent::getAsBoolean($key);
    }

    /**
     * @param non-empty-string $key
     */
    public function getAsInteger(string $key): int
    {
        return parent::getAsInteger($key);
    }

    /**
     * @param self::STATUS_* $status
     */
    public function setLoadStatus(int $status): void
    {
        parent::setLoadStatus($status);
    }
}
