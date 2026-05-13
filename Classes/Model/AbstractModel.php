<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Model;

use OliverKlee\Oelib\DataStructures\AbstractObjectWithAccessors;
use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Oelib\Interfaces\Identity;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class represents a general domain model which is capable of lazy loading (using ghosts).
 *
 * A model can have one of the following states: dead, ghost, loading, loaded, virgin.
 */
abstract class AbstractModel extends AbstractObjectWithAccessors implements Identity
{
    /**
     * a status indicating that this model has neither data nor UID yet
     */
    public const STATUS_VIRGIN = 0;

    /**
     * a status indicating that this model's data has not been
     * loaded yet (lazily), but that the model already has a UID
     */
    public const STATUS_GHOST = 1;

    /**
     * a status indicating that this model's data currently is being loaded
     */
    public const STATUS_LOADING = 2;

    /**
     * a status indicating that this model's data has already been loaded (with or without UID)
     */
    public const STATUS_LOADED = 3;

    /**
     * a status indicating that this model's data could not be retrieved from the DB
     */
    public const STATUS_DEAD = 4;

    /**
     * @var int<0, max> this model's UID, will be 0 if this model has been created in memory
     */
    private int $uid = 0;

    /**
     * data for this object (without the UID)
     *
     * @var array<non-empty-string, string|int|float|bool|object|null> $data
     */
    private array $data = [];

    /**
     * @var self::STATUS_*
     */
    private int $loadStatus = self::STATUS_VIRGIN;

    /**
     * @var bool whether this model's initial data has changed
     */
    private bool $isDirty = false;

    /**
     * @var \Closure|null the callback function that fills this model with data
     */
    private $loadCallback;

    /**
     * Clone.
     *
     * @throws \BadMethodCallException
     */
    public function __clone()
    {
        if ($this->isDead()) {
            throw new \BadMethodCallException('Deleted models cannot be cloned.', 1_436_453_107);
        }

        if ($this->isLoading()) {
            throw new \BadMethodCallException('Models cannot be cloned while they are loading.', 1_436_453_245);
        }

        if ($this->isGhost()) {
            $this->load();
        }

        $this->resetUid();

        /** @var int|string|bool|float|Collection<AbstractModel>|AbstractModel|null $dataItem */
        foreach ($this->data as $key => $dataItem) {
            if ($dataItem instanceof Collection) {
                /** Collection $dataItem */
                if ($dataItem->isRelationOwnedByParent()) {
                    $newDataItem = new Collection();
                    $newDataItem->markAsOwnedByParent();
                    /** @var AbstractModel $childModel */
                    foreach ($dataItem as $childModel) {
                        $newDataItem->add(clone $childModel);
                    }
                } else {
                    $newDataItem = clone $dataItem;
                }

                $newDataItem->setParentModel($this);
                $this->set($key, $newDataItem);
            }
        }

        $this->markAsDirty();
    }

    /**
     * Sets the complete data for this model.
     *
     * The data which is set via this function is considered to be the initial
     * data. Fields with relations must already be filled with the constituted
     * models/lists, not just with the UIDs (unlike the format that
     * AbstractDataMapper::getLoadedTestingModel takes).
     *
     * This function should be called directly after instantiation and must only
     * be called once. Usually, this function is called on only a few occasions:
     *
     * 1. when the data mapper loads a model
     * 2. when a new model is created in some unit tests
     * 3. before a new model should be saved to the database
     *
     * @param array<non-empty-string, string|int|float|bool|object|null> $data
     */
    public function setData(array $data): void
    {
        if ($this->isLoaded()) {
            throw new \BadMethodCallException('setData must only be called once per model instance.', 1_331_489_244);
        }

        $this->resetData($data);
    }

    /**
     * Sets the complete data for this model.
     *
     * This function may be called more than once.
     *
     * @param array<non-empty-string, string|int|float|bool|object|null> $data
     */
    public function resetData(array $data): void
    {
        $this->data = $data;
        if ($this->existsKey('uid')) {
            if (!$this->hasUid()) {
                $rawUid = $this->data['uid'];
                $uid = (\is_int($rawUid) || \is_string($rawUid)) ? (int)$rawUid : 0;
                if ($uid > 0) {
                    $this->setUid($uid);
                }
            }

            unset($this->data['uid']);
        }

        $this->markAsLoaded();
        if ($this->hasUid()) {
            $this->markAsClean();
        } else {
            $this->markAsDirty();
        }
    }

    /**
     * Returns the complete data for this model.
     *
     * This function may only be called by the mapper.
     *
     * @return array<string, string|int|float|bool|object|null> $data the model data, might be empty
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Marks this model as "loaded", i.e., that it has some real data.
     */
    protected function markAsLoaded(): void
    {
        $this->setLoadStatus(self::STATUS_LOADED);
    }

    /**
     * Marks this model as "dead", i.e., that retrieving its data from the DB has failed.
     */
    public function markAsDead(): void
    {
        $this->setLoadStatus(self::STATUS_DEAD);
        $this->markAsClean();
    }

    /**
     * Marks this model as loading.
     */
    private function markAsLoading(): void
    {
        $this->setLoadStatus(self::STATUS_LOADING);
    }

    /**
     * Sets this model's UID.
     *
     * This function may only be called on models that do not have a UID yet.
     *
     * If this function is called on an empty model, the model state is changed
     * to ghost.
     *
     * @param positive-int $uid the UID to set
     */
    public function setUid(int $uid): void
    {
        if ($this->hasUid()) {
            throw new \BadMethodCallException('The UID of a model cannot be set a second time.', 1_331_489_260);
        }

        if ($this->isVirgin()) {
            $this->setLoadStatus(self::STATUS_GHOST);
        }

        $this->uid = $uid;
    }

    /**
     * Resets the UID to 0, i.e., this model has no UID anymore.
     */
    private function resetUid(): void
    {
        $this->uid = 0;
    }

    /**
     * Sets the value of the data item for the given key.
     *
     * @param non-empty-string $key
     * @param string|int|float|bool|object|null $value the data for the given key
     */
    protected function set(string $key, $value): void
    {
        if ($key === 'deleted') {
            throw new \InvalidArgumentException(
                '$key must not be "deleted". Please use setToDeleted() instead.',
                1_331_489_276,
            );
        }

        if ($this->isGhost()) {
            $this->load();
        }

        $this->data[$key] = $value;

        $this->markAsLoaded();
        $this->markAsDirty();
    }

    /**
     * Gets the value of the data item for the given key.
     *
     * Before this function may be called, `setData()` or `set()` must have been called once.
     *
     * @param non-empty-string $key
     *
     * @return string|int|float|bool|object|null the data for the given key,
     *         will be an empty string if the key has not been set yet
     *
     * @throws NotFoundException if this model is dead
     */
    protected function get(string $key)
    {
        if ($key === 'uid') {
            throw new \InvalidArgumentException(
                'The UID column needs to be accessed using the getUid function.',
                1_331_489_310,
            );
        }

        $this->load();
        if ($this->isDead()) {
            throw new NotFoundException(
                'The ' . static::class . ' with the UID ' . $this->getUid() .
                ' either has been deleted (or has never existed), but still is accessed.',
                1_332_446_332,
            );
        }

        if (!$this->existsKey($key)) {
            return '';
        }

        return $this->data[$key];
    }

    /**
     * Checks whether a data item with a certain key exists.
     *
     * @param non-empty-string $key
     *
     * @return bool true if a data item with the key $key exists, false otherwise
     */
    protected function existsKey(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Gets the value stored under the given key as a model.
     *
     * @param non-empty-string $key
     *
     * @throws \UnexpectedValueException if there is a data item stored for the given key that is not a model instance
     */
    protected function getAsModel(string $key): ?AbstractModel
    {
        $this->checkForNonEmptyKey($key);

        $result = $this->get($key);
        if (($result === null) || !$this->existsKey($key)) {
            return null;
        }

        if (!$result instanceof self) {
            throw new \UnexpectedValueException(
                'The data item for the key "' . $key . '" is no model instance.',
                1_331_489_359,
            );
        }

        return $result;
    }

    /**
     * Gets the value stored under the given key as a collection of models.
     *
     * @param non-empty-string $key
     *
     * @return Collection<AbstractModel> the data item for the given key
     *
     * @throws \UnexpectedValueException if there is a data item stored for the given key that is not a collection
     *         or if that item has not been set yet
     */
    public function getAsCollection(string $key): Collection
    {
        $this->checkForNonEmptyKey($key);

        $result = $this->get($key);
        if (!$result instanceof Collection) {
            throw new \UnexpectedValueException(
                'The data item for the key "' . $key . '" is no collection.',
                1_331_489_379,
            );
        }

        return $result;
    }

    /**
     * Makes sure this model has some data by loading the data for ghost models.
     */
    private function load(): void
    {
        if ($this->isVirgin()) {
            throw new \BadMethodCallException(
                static::class . '#' . $this->getUid()
                . ': Please call setData() directly after instantiation first.',
                1_331_489_395,
            );
        }

        if ($this->isGhost()) {
            if (!$this->loadCallback instanceof \Closure) {
                throw new \BadMethodCallException(
                    'Ghosts need a load callback function before their data can be accessed.',
                    1_331_489_414,
                );
            }

            $this->markAsLoading();
            $callback = $this->loadCallback;
            if (!\is_callable($callback)) {
                throw new \RuntimeException('Model load callback is not callable.', 1_646_325_797);
            }

            $callback($this);
        }
    }

    /**
     * Gets this model's UID.
     *
     * @return int<0, max> this model's UID, will be zero if this model does not have a UID yet
     */
    public function getUid(): int
    {
        return $this->uid;
    }

    /**
     * Checks whether this model has a UID.
     *
     * @return bool TRUE if this model has a non-zero UID, FALSE otherwise
     */
    public function hasUid(): bool
    {
        return $this->getUid() > 0;
    }

    /**
     * @return self::STATUS_*
     */
    protected function getLoadStatus(): int
    {
        return $this->loadStatus;
    }

    /**
     * @param self::STATUS_* $status
     */
    protected function setLoadStatus(int $status): void
    {
        $this->loadStatus = $status;
    }

    /**
     * Checks whether this is a virgin model (which has neither data nor UID).
     *
     * @return bool TRUE if this is a virgin model, FALSE otherwise
     */
    public function isVirgin(): bool
    {
        return $this->getLoadStatus() === self::STATUS_VIRGIN;
    }

    /**
     * Checks whether this model is a ghost (has a UID, but is not fully loaded
     * yet).
     *
     * @return bool TRUE if this model is a ghost, FALSE otherwise
     */
    public function isGhost(): bool
    {
        return $this->getLoadStatus() === self::STATUS_GHOST;
    }

    /**
     * Checks whether this model is currently loading.
     *
     * @return bool TRUE if this model is loading, FALSE otherwise
     */
    public function isLoading(): bool
    {
        return $this->getLoadStatus() === self::STATUS_LOADING;
    }

    /**
     * Checks whether this model is fully loaded (has data).
     *
     * @return bool TRUE if this model is fully loaded, FALSE otherwise
     */
    public function isLoaded(): bool
    {
        return $this->getLoadStatus() === self::STATUS_LOADED;
    }

    /**
     * Checks whether this model is dead (retrieving its data from the DB has
     * failed).
     *
     * @return bool TRUE if this model is dead, FALSE otherwise
     */
    public function isDead(): bool
    {
        return $this->getLoadStatus() === self::STATUS_DEAD;
    }

    /**
     * Checks whether this model is hidden.
     *
     * @return bool TRUE if this model is hidden, FALSE otherwise
     */
    public function isHidden(): bool
    {
        return $this->getAsBoolean('hidden');
    }

    /**
     * Marks this model as hidden.
     */
    public function markAsHidden(): void
    {
        $this->setAsBoolean('hidden', true);
    }

    /**
     * Marks this model as visible (= not hidden).
     */
    public function markAsVisible(): void
    {
        $this->setAsBoolean('hidden', false);
    }

    /**
     * Sets the callback function for loading this model with data.
     *
     * @param \Closure $callback the callback function for loading this model with data
     */
    public function setLoadCallback(\Closure $callback): void
    {
        $this->loadCallback = $callback;
    }

    /**
     * Checks whether this model has a callback function set for loading its data.
     */

    /**
     * Marks this model's data as clean.
     */
    public function markAsClean(): void
    {
        $this->isDirty = false;
    }

    /**
     * Marks this model's data as dirty.
     */
    public function markAsDirty(): void
    {
        $this->isDirty = true;
    }

    /**
     * Checks whether this model has been marked as dirty which means that this
     * model's data has changed compared to the initial state.
     *
     * @return bool TRUE if this model has been marked as dirty
     */
    public function isDirty(): bool
    {
        return $this->isDirty;
    }

    /**
     * Sets the "deleted" property for the current model.
     *
     * Note: This function is intended to be called only by a data mapper.
     *
     * @internal
     *
     * @deprecated #1678 will be removed in version 7.0
     */
    public function setToDeleted(): void
    {
        if ($this->isLoaded()) {
            $this->data['deleted'] = true;
            $this->markAsDirty();
        } else {
            $this->markAsDead();
        }
    }

    /**
     * Checks whether this model is set to be deleted.
     *
     * @return bool TRUE if this model is set to deleted, FALSE otherwise
     */
    public function isDeleted(): bool
    {
        return $this->getAsBoolean('deleted');
    }

    /**
     * @return int<0, max>
     *
     * @deprecated with be removed in version 7.0 with #1971
     */
    public function getModificationDateAsUnixTimeStamp(): int
    {
        return $this->getAsNonNegativeInteger('tstamp');
    }

    /**
     * Sets the modification date and time.
     *
     * @deprecated #1678 will be removed in version 7.0
     */
    public function setTimestamp(): void
    {
        $timestamp = GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('date', 'timestamp');
        \assert(\is_int($timestamp));
        $this->setAsInteger('tstamp', $timestamp);
    }

    /**
     * @return int<0, max>
     *
     * @deprecated with be removed in version 7.0 with #1971
     */
    public function getCreationDateAsUnixTimeStamp(): int
    {
        return $this->getAsNonNegativeInteger('crdate');
    }

    /**
     * Sets the creation date and time.
     *
     * @deprecated #1678 will be removed in version 7.0
     */
    public function setCreationDate(): void
    {
        if ($this->hasUid()) {
            throw new \BadMethodCallException('Only new objects (without UID) may receive "crdate".', 1_331_489_449);
        }

        $timestamp = GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('date', 'timestamp');
        \assert(\is_int($timestamp));
        $this->setAsInteger('crdate', $timestamp);
    }

    /**
     * Returns the page UID of this model.
     *
     * @return int<0, max>
     */
    public function getPageUid(): int
    {
        return $this->getAsNonNegativeInteger('pid');
    }

    /**
     * Sets this model's page UID.
     *
     * @param int<0, max> $pageUid the page to set
     */
    public function setPageUid(int $pageUid): void
    {
        // @phpstan-ignore-next-line We're explicitly checking for a contract violation here.
        if ($pageUid < 0) {
            throw new \InvalidArgumentException('$pageUid must be >= 0.', 2440827722);
        }

        $this->setAsInteger('pid', $pageUid);
    }

    /**
     * Checks whether this model is empty.
     */
    public function isEmpty(): bool
    {
        if ($this->isGhost()) {
            $this->load();
            $this->markAsLoaded();
        }

        return $this->data === [];
    }
}
