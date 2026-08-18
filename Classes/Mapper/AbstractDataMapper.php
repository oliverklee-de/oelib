<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Mapper;

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Oelib\Model\AbstractModel;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class represents a mapper that maps database record to model instances.
 *
 * @template M of AbstractModel
 *
 * @phpstan-type DatabaseColumn string|int|float|bool|null
 * @phpstan-type DatabaseRow array<string, DatabaseColumn>
 */
abstract class AbstractDataMapper
{
    /**
     * @var non-empty-string the name of the database table for this mapper
     */
    protected $tableName;

    /**
     * @var class-string<M> the model class name for this mapper
     */
    protected $modelClassName;

    /**
     * @var non-empty-string a comma-separated list of DB column names to retrieve or "*" for all columns
     */
    protected $columns = '*';

    /**
     * @var IdentityMap a map that holds the models that already have been retrieved
     */
    private IdentityMap $map;

    /**
     * @var array<positive-int, true> UIDs of models that are memory-only models that must not be saved,
     *      using the UIDs as keys and TRUE as value
     */
    private array $uidsOfMemoryOnlyDummyModels = [];

    /**
     * @var array<non-empty-string, class-string<AbstractDataMapper>>
     *      the (possible) relations of the created models in the format DB column name => mapper name
     */
    protected $relations = [];

    /**
     * @var array<non-empty-string> the column names of additional string keys
     *
     * @deprecated #1992 will be removed in version 7.0
     */
    protected $additionalKeys = [];

    /**
     * @var array<string, array<string, M>> two-dimensional cache for the objects by key:
     *            `[key name][key value] => model`
     *
     * @deprecated #1992 will be removed in version 7.0
     */
    private array $cacheByKey = [];

    /**
     * The constructor.
     */
    public function __construct()
    {
        // @phpstan-ignore-next-line We are explicitly testing for a contract violation here.
        if ($this->getTableName() === '') {
            throw new \InvalidArgumentException(static::class . '::tableName must not be empty.', 1_331_319_361);
        }

        // @phpstan-ignore-next-line We are explicitly testing for a contract violation here.
        if ($this->columns === '') {
            throw new \InvalidArgumentException(static::class . '::columns must not be empty.', 1_331_319_374);
        }

        if (!\is_string($this->modelClassName)) {
            throw new \InvalidArgumentException(static::class . '::modelClassName must not be empty.', 1_331_319_378);
        }

        $this->map = new IdentityMap();

        foreach ($this->additionalKeys as $key) {
            $this->cacheByKey[$key] = [];
        }
    }

    /**
     * Retrieves a model for the record with the UID $uid. If that particular
     * model already is cached in memory, the cached instance is returned.
     *
     * The model may still be a ghost which will get fully initialized once its
     * data is accessed.
     *
     * Note: This function does not check that a record with the UID $uid
     * actually exists in the database.
     *
     * @param positive-int $uid the UID of the record to retrieve
     *
     * @return M the model with the UID $uid
     */
    public function find(int $uid): AbstractModel
    {
        try {
            /** @var M $model */
            $model = $this->map->get($uid);
        } catch (NotFoundException $notFoundException) {
            $model = $this->createGhost($uid);
        }

        return $model;
    }

    /**
     * Returns a model for the provided array. If the UID provided with the
     * array is already mapped, this yet existing model will be returned
     * irrespective of the other provided data, otherwise the model will be
     * loaded with the provided data.
     *
     * @param DatabaseRow $data data for the model to return, must at least contain the UID
     *
     * @return M model for the given UID, filled with data provided in case it did not have any data in memory before
     */
    public function getModel(array $data): AbstractModel
    {
        if (!isset($data['uid'])) {
            throw new \InvalidArgumentException('$data must contain an element "uid".', 1_331_319_491);
        }

        $uid = (int)$data['uid'];
        if ($uid <= 0) {
            throw new \InvalidArgumentException('$data["uid"] must be a positive integer.', 1_699_655_040);
        }

        $model = $this->find($uid);

        if ($model->isGhost()) {
            $this->fillModel($model, $data);
        }

        return $model;
    }

    /**
     * Returns a list of models for the provided two-dimensional array with model data.
     *
     * @param DatabaseRow[] $dataOfModels two-dimensional array,
     *        each inner array must at least contain the element "uid", may be empty
     *
     * @return Collection<M>
     *         Models with the UIDs provided. The models will be filled with the
     *         data provided in case they did not have any data before,
     *         otherwise the already loaded data will be used. If $dataOfModels
     *         was empty, an empty list will be returned.
     *
     * @see getModel()
     */
    public function getListOfModels(array $dataOfModels): Collection
    {
        /** @var Collection<M> $list */
        $list = new Collection();

        foreach ($dataOfModels as $modelRecord) {
            $list->add($this->getModel($modelRecord));
        }

        return $list;
    }

    /**
     * Retrieves a model based on the WHERE clause given in the parameter
     * $whereClauseParts. Hidden records will be retrieved as well.
     *
     * @param non-empty-array<string, string|int> $whereClauseParts WHERE clause parts for the record to retrieve,
     *        each element must consist of a column name as key and a value to search for as value
     *        (will automatically get quoted)
     *
     * @return M the model
     *
     * @throws NotFoundException if there is no record in the DB which matches the WHERE clause
     */
    protected function findSingleByWhereClause(array $whereClauseParts): AbstractModel
    {
        // @phpstan-ignore-next-line We are explicitly testing for a contract violation here.
        if ($whereClauseParts === []) {
            throw new \InvalidArgumentException('The parameter $whereClauseParts must not be empty.', 1_331_319_506);
        }

        return $this->getModel($this->retrieveRecord($whereClauseParts));
    }

    /**
     * Checks whether a model with a certain UID actually exists in the database
     * and could be loaded.
     *
     * @param positive-int $uid the UID of the record to retrieve
     * @param bool $allowHidden whether hidden records should be allowed to be retrieved
     *
     * @return bool TRUE if a model with the UID $uid exists in the database,
     *                 FALSE otherwise
     */
    public function existsModel(int $uid, bool $allowHidden = false): bool
    {
        $model = $this->find($uid);

        if ($model->isGhost()) {
            $this->load($model);
        }

        return $model->isLoaded() && (!$model->isHidden() || $allowHidden);
    }

    /**
     * Loads a model's data from the database (retrieved by using the
     * model's UID) and fills the model with it.
     *
     * If a model's data cannot be retrieved from the DB, the model will be set
     * to the "dead" state.
     *
     * Note: This method may only be called at most once per model instance.
     *
     * @param M $model the model to fill, must already have a UID
     *
     * @throws \InvalidArgumentException if $model has no UID or has been created via getNewGhost
     */
    public function load(AbstractModel $model): void
    {
        if ($this->isModelAMemoryOnlyDummy($model)) {
            throw new \InvalidArgumentException(
                'This ghost was created via getNewGhost and must not be loaded.',
                1_331_319_529,
            );
        }

        if (!$model->hasUid()) {
            throw new \InvalidArgumentException(
                'load must only be called with models that already have a UID.',
                1_331_319_554,
            );
        }

        $uid = $model->getUid();
        \assert($uid > 0);
        try {
            $data = $this->retrieveRecordByUid($uid);
            $this->fillModel($model, $data);
        } catch (NotFoundException $notFoundException) {
            $model->markAsDead();
        }
    }

    /**
     * Fills a model with data, including the relations.
     *
     * This function also updates the cache-by-key.
     *
     * This method must be called at most once per model instance.
     *
     * @param M $model the model to fill, needs to have a UID
     * @param DatabaseRow $data the model data to process as it comes from the DB
     */
    private function fillModel(AbstractModel $model, array $data): void
    {
        $this->cacheModelByKeys($model, $data);
        $this->createRelations($data, $model);
        $model->setData($data);
    }

    /**
     * Processes a model's data and creates any relations that are hidden within
     * it using foreign key mapping.
     *
     * @param array<string, string|int> $data the model data to process, might be modified
     * @param M $model the model to create the relations for
     */
    protected function createRelations(array &$data, AbstractModel $model): void
    {
        foreach (array_keys($this->relations) as $key) {
            if ($this->isOneToManyRelationConfigured($key)) {
                $this->createOneToManyRelation($data, $key, $model);
            } elseif ($this->isManyToOneRelationConfigured($key)) {
                $this->createManyToOneRelation($data, $key);
            } elseif ($this->isManyToManyRelationConfigured($key)) {
                $this->createMToNRelation($data, $key, $model);
            } else {
                $this->createCommaSeparatedRelation($data, $key, $model);
            }
        }
    }

    /**
     * Retrieves the configuration of a relation from the TCA.
     *
     * @param non-empty-string $key the key of the relation to retrieve
     *
     * @return array<string, string> configuration for that relation, will not be empty if the TCA is valid
     *
     * @throws \BadMethodCallException
     */
    private function getRelationConfigurationFromTca(string $key): array
    {
        $tca = $this->getTcaForTable($this->getTableName());

        if (!isset($tca['columns'][$key])) {
            throw new \BadMethodCallException(
                'In the table ' . $this->getTableName() . ', the column ' . $key . ' does not have a TCA entry.',
                1_331_319_627,
            );
        }

        return $tca['columns'][$key]['config'];
    }

    /**
     * Checks whether the relation is configured in the TCA to be an 1:n
     * relation.
     *
     * @param non-empty-string $key key of the relation
     *
     * @return bool TRUE if the relation is an 1:n relation, FALSE otherwise
     */
    private function isOneToManyRelationConfigured(string $key): bool
    {
        $relationConfiguration = $this->getRelationConfigurationFromTca($key);

        return isset($relationConfiguration['foreign_field'], $relationConfiguration['foreign_table'])
            && $this->possiblyAllowsMultipleSelectionByType($key);
    }

    /**
     * Checks whether the relation is configured in the TCA to be an n:1
     * relation.
     *
     * @param non-empty-string $key key of the relation
     *
     * @return bool TRUE if the relation is an n:1 relation, FALSE otherwise
     */
    private function isManyToOneRelationConfigured(string $key): bool
    {
        $relationConfiguration = $this->getRelationConfigurationFromTca($key);
        $cardinality = (int)($relationConfiguration['maxitems'] ?? 0);
        if ($cardinality === 0) {
            $cardinality = $this->possiblyAllowsMultipleSelectionByType($key) ? 99999 : 1;
        }

        return $cardinality === 1;
    }

    /**
     * Checks whether there is a table for an m:n relation configured in the
     * TCA.
     *
     * @param non-empty-string $key key of the relation
     *
     * @return bool TRUE if the relation's configuration provides an m:n table, FALSE otherwise
     */
    private function isManyToManyRelationConfigured(string $key): bool
    {
        $relationConfiguration = $this->getRelationConfigurationFromTca($key);

        return isset($relationConfiguration['MM']);
    }

    /**
     * @param non-empty-string $key
     */
    private function possiblyAllowsMultipleSelectionByType(string $key): bool
    {
        $relationConfiguration = $this->getRelationConfigurationFromTca($key);

        if (!\in_array($relationConfiguration['type'], ['select', 'inline', 'group'], true)) {
            return false;
        }

        $renderType = ($relationConfiguration['renderType'] ?? '');
        return $renderType !== 'selectSingle';
    }

    /**
     * Creates an 1:n relation using foreign field mapping.
     *
     * @param array<string, mixed> $data the model data to process, will be modified
     * @param non-empty-string $key the key of the data item for which the relation should be created
     * @param M $model the model to create the relation for
     *
     * @throws \UnexpectedValueException
     */
    private function createOneToManyRelation(array &$data, string $key, AbstractModel $model): void
    {
        $modelData = [];

        $dataItem = (int)($data[$key] ?? 0);
        if ($dataItem > 0) {
            if ($this->isModelAMemoryOnlyDummy($model)) {
                throw new \InvalidArgumentException(
                    'This is a memory-only dummy which must not load any one-to-many relations from the database.',
                    1_331_319_658,
                );
            }

            $relationConfiguration = $this->getRelationConfigurationFromTca($key);
            $foreignTable = $relationConfiguration['foreign_table'] ?? '';
            if ($foreignTable === '') {
                throw new \UnexpectedValueException('"foreign_table" is missing in the TCA.', 1_646_234_422);
            }

            $foreignField = $relationConfiguration['foreign_field'] ?? '';
            if (($relationConfiguration['foreign_sortby'] ?? '') !== '') {
                $sortingField = $relationConfiguration['foreign_sortby'];
            } elseif (($relationConfiguration['foreign_default_sortby'] ?? '') !== '') {
                $sortingField = $relationConfiguration['foreign_default_sortby'];
            } else {
                $sortingField = '';
            }

            $orderBy = $sortingField !== '' ? [$sortingField => 'ASC'] : [];
            $queryResult = $this
                ->getConnectionForTable($foreignTable)
                ->select(['*'], $foreignTable, [$foreignField => (int)($data['uid'] ?? 0)], [], $orderBy);
            /** @var DatabaseRow[] $modelData */
            $modelData = $queryResult->fetchAllAssociative();
        }

        /** @var Collection<AbstractModel> $models */
        $models = $this->getRelationMapperByKey($key)->getListOfModels($modelData);
        $models->setParentModel($model);
        $models->markAsOwnedByParent();
        $data[$key] = $models;
    }

    /**
     * Creates an n:1 relation using foreign key mapping.
     *
     * @param array<string, mixed> $data the model data to process, will be modified
     * @param non-empty-string $key the key of the data item for which the relation should be created
     */
    private function createManyToOneRelation(array &$data, string $key): void
    {
        $uid = (int)($data[$key] ?? 0);

        $data[$key] = $uid > 0 ? $this->getRelationMapperByKey($key)->find($uid) : null;
    }

    /**
     * Creates an n:1 relation using a comma-separated list of UIDs.
     *
     * @param array<string, mixed> $data the model data to process, will be modified
     * @param non-empty-string $key the key of the data item for which the relation should be created
     * @param M $model the model to create the relation for
     */
    private function createCommaSeparatedRelation(array &$data, string $key, AbstractModel $model): void
    {
        $list = new Collection();
        $list->setParentModel($model);

        $uidList = \trim((string)($data[$key] ?? ''));
        if ($uidList !== '') {
            $mapper = $this->getRelationMapperByKey($key);
            foreach (GeneralUtility::intExplode(',', $uidList, true) as $uid) {
                // Some relations might have a junk 0 in it. We ignore it to avoid crashing.
                if ($uid <= 0) {
                    continue;
                }

                $list->add($mapper->find($uid));
            }
        }

        $data[$key] = $list;
    }

    /**
     * Creates an m:n relation using an m:n table.
     *
     * Note: This doesn't work for the reverse direction of bidirectional
     * relations yet.
     *
     * @param array<string, mixed> $data the model data to process, will be modified
     * @param non-empty-string $key the key of the data item for which the relation should be created
     * @param M $model the model to create the relation for
     */
    private function createMToNRelation(array &$data, string $key, AbstractModel $model): void
    {
        $list = new Collection();
        $list->setParentModel($model);

        $dataItem = (int)($data[$key] ?? 0);
        if ($dataItem > 0) {
            $mapper = $this->getRelationMapperByKey($key);
            $relationConfiguration = $this->getRelationConfigurationFromTca($key);
            $mnTable = $relationConfiguration['MM'] ?? '';
            if ($mnTable === '') {
                throw new \UnexpectedValueException('MM relation information missing.', 1_646_236_363);
            }

            $rightUid = (int)($data['uid'] ?? 0);
            if (isset($relationConfiguration['MM_opposite_field'])) {
                $leftColumn = 'uid_local';
                $rightColumn = 'uid_foreign';
                $orderBy = 'uid_local';
            } else {
                $leftColumn = 'uid_foreign';
                $rightColumn = 'uid_local';
                $orderBy = 'sorting';
            }

            $queryResult = $this
                ->getConnectionForTable($mnTable)
                ->select([$leftColumn], $mnTable, [$rightColumn => $rightUid], [], [$orderBy => 'ASC']);
            foreach (\array_column($queryResult->fetchAllAssociative(), $leftColumn) as $relationUid) {
                // Some relations might have a junk 0 in it. We ignore it to avoid crashing.
                if ((int)$relationUid <= 0) {
                    continue;
                }

                $list->add($mapper->find((int)$relationUid));
            }
        }

        $data[$key] = $list;
    }

    /**
     * Reads a record from the database (from this mapper's table) by the
     * WHERE clause provided. Hidden records will be retrieved as well.
     *
     * @param non-empty-array<string, string|int> $whereClauseParts
     *        WHERE clause parts for the record to retrieve, each element must consist of a column name as key and a
     *        value to search for as value (will automatically get quoted)
     *
     * @return DatabaseRow the record from the database, will not be empty
     *
     * @throws NotFoundException if there is no record in the DB which matches the WHERE clause
     * @throws NotFoundException if database access is disabled
     */
    protected function retrieveRecord(array $whereClauseParts): array
    {
        $tableName = $this->getTableName();
        $query = $this->getQueryBuilderForTable($tableName);
        $query->getRestrictions()->removeByType(HiddenRestriction::class);
        $query->select('*')->from($tableName);
        foreach ($whereClauseParts as $identifier => $value) {
            $query->andWhere($query->expr()->eq($identifier, $query->createNamedParameter($value)));
        }

        /** @var DatabaseRow|false $data */
        $data = $query->executeQuery()->fetchAssociative();
        if (!\is_array($data)) {
            throw new NotFoundException(
                'No records found in the table "' . $tableName . '" matching: ' . \json_encode(
                    $whereClauseParts,
                    JSON_THROW_ON_ERROR,
                ),
                8074950578,
            );
        }

        return $data;
    }

    /**
     * Reads a record from the database by UID (from this mapper's table).
     * Hidden records will be retrieved as well.
     *
     * @param positive-int $uid the UID of the record to retrieve
     *
     * @return DatabaseRow the record from the database, will not be empty
     *
     * @throws NotFoundException if there is no record in the DB with the UID $uid
     */
    protected function retrieveRecordByUid(int $uid): array
    {
        return $this->retrieveRecord(['uid' => $uid]);
    }

    /**
     * Creates a new ghost model with the UID $uid and registers it.
     *
     * @param positive-int $uid the UID of the to-create ghost
     *
     * @return M a ghost model with the UID $uid
     */
    protected function createGhost(int $uid): AbstractModel
    {
        $model = GeneralUtility::makeInstance($this->modelClassName);
        $model->setUid($uid);

        $callback = function (AbstractModel $model): void {
            /** @var M $model */
            $this->load($model);
        };
        $model->setLoadCallback($callback);
        $this->map->add($model);

        return $model;
    }

    /**
     * Creates a new registered ghost with a UID that has not been used in this
     * data mapper yet.
     *
     * Important: As this ghost's UID has nothing to do with the real UIDs in
     * the database, this ghost must not be loaded or saved.
     *
     * @return M a new ghost
     */
    public function getNewGhost(): AbstractModel
    {
        $model = $this->createGhost($this->map->getNewUid());
        $this->registerModelAsMemoryOnlyDummy($model);

        return $model;
    }

    /**
     * Creates a new registered model with a UID that has not been used in this
     * data mapper yet and loads it with the data provided in $data.
     *
     * The data is considered to be in the same format as in the database,
     * e.g., m:1 relations are provided as the foreign UID, not as the constituted
     * model.
     *
     * (AbstractModel::setData works differently: There you need to provide the
     * data with the relations already being the model/list objects.)
     *
     * This function should only be used in unit tests for mappers (to avoid
     * creating records in the DB when the DB access itself needs not be
     * tested).
     *
     * To use this function for testing relations to the same mapper, the mapper
     * needs to be accessed via the mapper registry so object identity is
     * ensured.
     *
     * Important: As this model's UID has nothing to do with the real UIDs in
     * the database, this model must not be saved.
     *
     * @param array<string, string|int> $data the data as it would come from the database, may be empty
     *
     * @return M a new model loaded with $data
     */
    public function getLoadedTestingModel(array $data): AbstractModel
    {
        $model = $this->getNewGhost();
        $this->fillModel($model, $data);

        return $model;
    }

    /**
     * Writes a model to the database. Does nothing if database access is
     * denied, if the model is clean, if the model has status dead, virgin or
     * ghost, if the model is read-only or if there is no data to set.
     *
     * @param M $model the model to write to the database
     *
     * @deprecated #1678 will be removed in version 7.0
     */
    public function save(AbstractModel $model): void
    {
        if ($this->isModelAMemoryOnlyDummy($model)) {
            throw new \InvalidArgumentException(
                'This model is a memory-only dummy that must not be saved.',
                1_331_319_682,
            );
        }

        if (!$model->isDirty() || !$model->isLoaded()) {
            return;
        }

        $data = $this->getPreparedModelData($model);
        $this->cacheModelByKeys($model, $data);

        if ($model->hasUid()) {
            $this->getConnection()->update($this->getTableName(), $data, ['uid' => $model->getUid()]);
            $this->deleteManyToManyRelationIntermediateRecords($model);
        } else {
            $tableName = $this->getTableName();
            $this->getConnection()->insert($tableName, $data);
            $lastInsertId = (int)$this->getConnection()->lastInsertId($tableName);
            if ($lastInsertId <= 0) {
                throw new \UnexpectedValueException('No last insert ID available.', 1_699_640_499);
            }

            $model->setUid($lastInsertId);
            $this->map->add($model);
        }

        if ($model->isDeleted()) {
            $model->markAsDead();
        } else {
            $model->markAsClean();
            // We save the 1:n relations after marking this model as clean
            // in order to avoid infinite loops when the foreign model tries
            // to save this parent.
            $this->saveOneToManyRelationRecords($model);
            $this->createManyToManyRelationIntermediateRecords($model);
        }
    }

    /**
     * Prepares the model's data for the database. Changes the relations into a
     * database-applicable format. Sets the timestamp and sets the "crdate" for
     * new models.
     *
     * @param M $model the model to write to the database
     *
     * @return DatabaseRow the model's data prepared for the database, will not be empty
     *
     * @deprecated #1678 will be removed in version 7.0
     */
    private function getPreparedModelData(AbstractModel $model): array
    {
        if (!$model->hasUid()) {
            $model->setCreationDate();
        }

        $model->setTimestamp();

        $data = $model->getData();

        foreach (array_keys($this->relations) as $key) {
            $dataItem = $data[$key] ?? null;
            $relatedMapper = $this->getRelationMapperByKey($key);
            if ($this->isOneToManyRelationConfigured($key)) {
                $methodName = 'count';
            } elseif ($this->isManyToOneRelationConfigured($key)) {
                $methodName = 'getUid';

                if ($dataItem instanceof AbstractModel) {
                    $this->saveManyToOneRelatedModels($dataItem, $relatedMapper);
                }
            } else {
                $methodName = $this->isManyToManyRelationConfigured($key) ? 'count' : 'getUids';

                if ($dataItem instanceof Collection) {
                    $this->saveManyToManyAndCommaSeparatedRelatedModels($dataItem, $relatedMapper);
                }
            }

            // @phpstan-ignore-next-line This variable method access is okay.
            $data[$key] = \is_object($dataItem) ? $dataItem->{$methodName}() : 0;
        }

        foreach ($data as &$dataItem) {
            if (\is_bool($dataItem)) {
                $dataItem = (int)$dataItem;
            }
        }

        return $data;
    }

    /**
     * Saves the related model of an n:1-relation.
     *
     * @param AbstractModel $model the model to save
     * @param AbstractDataMapper $mapper the mapper to use for saving
     *
     * @deprecated #1678 will be removed in version 7.0
     */
    private function saveManyToOneRelatedModels(AbstractModel $model, AbstractDataMapper $mapper): void
    {
        $mapper->save($model);
    }

    /**
     * Saves the related models of a comma-separated and a regular m:n relation.
     *
     * @param Collection<AbstractModel> $list the list of models to save
     * @param AbstractDataMapper $mapper the mapper to use for saving
     *
     * @deprecated #1678 will be removed in version 7.0
     */
    private function saveManyToManyAndCommaSeparatedRelatedModels(Collection $list, AbstractDataMapper $mapper): void
    {
        /** @var AbstractModel $model */
        foreach ($list as $model) {
            $mapper->save($model);
        }
    }

    /**
     * Deletes the records in the intermediate table of m:n relations for a
     * given model.
     *
     * @param M $model the model to delete the records in the intermediate table of m:n relations for
     *
     * @deprecated #1678 will be removed in version 7.0
     */
    private function deleteManyToManyRelationIntermediateRecords(AbstractModel $model): void
    {
        foreach (array_keys($this->relations) as $key) {
            if (!$this->isManyToManyRelationConfigured($key)) {
                continue;
            }

            $relationConfiguration = $this->getRelationConfigurationFromTca($key);
            $mnTable = $relationConfiguration['MM'] ?? '';
            if ($mnTable === '') {
                throw new \UnexpectedValueException('MM relation information missing.', 1_646_236_349);
            }

            $columnName = isset($relationConfiguration['MM_opposite_field']) ? 'uid_foreign' : 'uid_local';
            $this->getConnectionForTable($mnTable)->delete($mnTable, [$columnName => $model->getUid()]);
        }
    }

    /**
     * Creates records in the intermediate table of m:n relations for a given model.
     *
     * @param M $model the model to create the records in the intermediate table of m:n relations for
     *
     * @deprecated #1678 will be removed in version 7.0
     */
    private function createManyToManyRelationIntermediateRecords(AbstractModel $model): void
    {
        $data = $model->getData();

        foreach (\array_keys($this->relations) as $key) {
            $dataItem = $data[$key] ?? null;
            if (!($dataItem instanceof Collection) || !$this->isManyToManyRelationConfigured($key)) {
                continue;
            }

            $sorting = 0;
            $relationConfiguration = $this->getRelationConfigurationFromTca($key);
            $mnTable = $relationConfiguration['MM'] ?? '';
            if ($mnTable === '') {
                throw new \UnexpectedValueException('MM relation information missing.', 1_646_236_298);
            }

            /** @var AbstractModel $relatedModel */
            foreach ($dataItem as $relatedModel) {
                if (isset($relationConfiguration['MM_opposite_field'])) {
                    $uidLocal = $relatedModel->getUid();
                    $uidForeign = $model->getUid();
                } else {
                    $uidLocal = $model->getUid();
                    $uidForeign = $relatedModel->getUid();
                }

                $newData = $this->getManyToManyRelationIntermediateRecordData($uidLocal, $uidForeign, $sorting);
                $this->getConnectionForTable($mnTable)->insert($mnTable, $newData);
                ++$sorting;
            }
        }
    }

    /**
     * Saves records that this model relates to as 1:n.
     *
     * @param M $model the model to save the related records for
     *
     * @deprecated #1678 will be removed in version 7.0
     */
    private function saveOneToManyRelationRecords(AbstractModel $model): void
    {
        $data = $model->getData();

        foreach (array_keys($this->relations) as $key) {
            if (!$this->isOneToManyRelationConfigured($key)) {
                continue;
            }

            $relatedModels = $data[$key] ?? null;
            if (!$relatedModels instanceof Collection) {
                continue;
            }

            $relatedMapper = $this->getRelationMapperByKey($key);
            $relationConfiguration = $this->getRelationConfigurationFromTca($key);
            $foreignField = $relationConfiguration['foreign_field'] ?? '';
            if ($foreignField === '') {
                throw new \BadMethodCallException(
                    'The relation ' . $this->getTableName() . ':' . $key . ' is missing the "foreign_field" setting.',
                    1_331_319_719,
                );
            }

            if (str_starts_with($foreignField, 'tx_')) {
                $foreignKey = \ucfirst((string)\preg_replace('/tx_[a-z]+_/', '', $foreignField));
            } else {
                $foreignKey = \ucfirst($foreignField);
            }

            $getter = 'get' . $foreignKey;
            $setter = 'set' . $foreignKey;

            /** @var AbstractModel $relatedModel */
            foreach ($relatedModels->toArray() as $relatedModel) {
                if (!method_exists($relatedModel, $getter)) {
                    throw new \BadMethodCallException(
                        'The class ' . $relatedModel::class . ' is missing the function ' . $getter
                        . ' which is needed for saving a 1:n relation.',
                        1_331_319_751,
                    );
                }

                if (!method_exists($relatedModel, $setter)) {
                    throw new \BadMethodCallException(
                        'The class ' . $relatedModel::class . ' is missing the function ' . $setter
                        . ' which is needed for saving a 1:n relation.',
                        1_331_319_803,
                    );
                }

                // @phpstan-ignore-next-line This variable method access is okay.
                if ($relatedModel->$getter() !== $model) {
                    // Only sets the model if this would change anything. This avoids marking unchanged models as dirty.
                    // @phpstan-ignore-next-line This variable method access is okay.
                    $relatedModel->$setter($model);
                }

                $relatedMapper->save($relatedModel);

                $unconnectedModels = $relatedMapper->findAllByRelation($model, $foreignField, $relatedModels);
                /** @var AbstractModel $unconnectedModel */
                foreach ($unconnectedModels as $unconnectedModel) {
                    $relatedMapper->delete($unconnectedModel);
                }
            }
        }
    }

    /**
     * Returns the record data for an intermediate m:n-relation record.
     *
     * @param int $uidLocal the UID of the local record
     * @param int $uidForeign the UID of the foreign record
     * @param int $sorting the sorting of the intermediate m:n-relation record
     *
     * @return array{uid_local: int, uid_foreign: int, sorting: int} record data for an intermediate m:n-relation record
     */
    protected function getManyToManyRelationIntermediateRecordData(int $uidLocal, int $uidForeign, int $sorting): array
    {
        return ['uid_local' => $uidLocal, 'uid_foreign' => $uidForeign, 'sorting' => $sorting];
    }

    /**
     * Marks `$model` as deleted and saves it to the DB (if it has a UID).
     *
     * @param M $model the model to delete, must not be a memory-only dummy, must not be read-only
     *
     * @internal
     *
     * @deprecated #1678 will be removed in version 7.0
     */
    public function delete(AbstractModel $model): void
    {
        if ($this->isModelAMemoryOnlyDummy($model)) {
            throw new \InvalidArgumentException(
                'This model is a memory-only dummy that must not be deleted.',
                1_331_319_817,
            );
        }

        if ($model->isDead()) {
            return;
        }

        if ($model->hasUid()) {
            if (!$model->isLoaded()) {
                $this->load($model);
            }

            $model->setToDeleted();
            $this->save($model);
            $this->deleteOneToManyRelations($model);
        }

        $model->markAsDead();
    }

    /**
     * Deletes all one-to-many related models of this model.
     *
     * @param M $model the model for which to delete the related models
     *
     * @deprecated #1678 will be removed in version 7.0
     */
    private function deleteOneToManyRelations(AbstractModel $model): void
    {
        $data = $model->getData();

        foreach (array_keys($this->relations) as $key) {
            if ($this->isOneToManyRelationConfigured($key)) {
                $relatedModels = $data[$key] ?? null;
                if (!$relatedModels instanceof Collection) {
                    continue;
                }

                $relatedMapper = $this->getRelationMapperByKey($key);
                foreach ($relatedModels as $relatedModel) {
                    $relatedMapper->delete($relatedModel);
                }
            }
        }
    }

    /**
     * Registers a model as a memory-only dummy that must not be saved.
     *
     * @param M $model the model to register
     */
    private function registerModelAsMemoryOnlyDummy(AbstractModel $model): void
    {
        if (!$model->hasUid()) {
            return;
        }

        $uid = $model->getUid();
        \assert($uid > 0);
        $this->uidsOfMemoryOnlyDummyModels[$uid] = true;
    }

    /**
     * Checks whether $model is a memory-only dummy that must not be saved
     *
     * @param M $model the model to check
     *
     * @return bool TRUE if $model is a memory-only dummy, FALSE otherwise
     */
    private function isModelAMemoryOnlyDummy(AbstractModel $model): bool
    {
        if (!$model->hasUid()) {
            return false;
        }

        return isset($this->uidsOfMemoryOnlyDummyModels[$model->getUid()]);
    }

    /**
     * Looks up a model in the cache by key.
     *
     * When this function reports "no match", the model could still exist in the
     * database, though.
     *
     * @param non-empty-string $key an existing key
     * @param non-empty-string $value the value for the key of the model to find
     *
     * @return M the cached model
     *
     * @throws NotFoundException if there is no match in the cache yet
     * @throws \InvalidArgumentException
     */
    protected function findOneByKeyFromCache(string $key, string $value): AbstractModel
    {
        // @phpstan-ignore-next-line We are explicitly testing for a contract violation here.
        if ($key === '') {
            throw new \InvalidArgumentException('$key must not be empty.', 1_416_847_364);
        }

        if (!isset($this->cacheByKey[$key])) {
            throw new \InvalidArgumentException('"' . $key . '" is not a valid key for this mapper.', 1_331_319_882);
        }

        // @phpstan-ignore-next-line We are explicitly testing for a contract violation here.
        if ($value === '') {
            throw new \InvalidArgumentException('$value must not be empty.', 1_331_319_892);
        }

        if (!isset($this->cacheByKey[$key][$value])) {
            throw new NotFoundException('Not found', 1_573_836_483);
        }

        return $this->cacheByKey[$key][$value];
    }

    /**
     * Puts a model in the cache-by-keys (if the model has any non-empty additional keys).
     *
     * @param M $model the model to cache
     * @param DatabaseRow $data the data of the model as it is in the DB, may be empty
     */
    private function cacheModelByKeys(AbstractModel $model, array $data): void
    {
        foreach ($this->additionalKeys as $key) {
            $dataItem = $data[$key] ?? null;
            if ($dataItem !== null) {
                $value = (string)$dataItem;
                if ($value !== '') {
                    $this->cacheByKey[$key][$value] = $model;
                }
            }
        }

        $this->cacheModelByCombinedKeys($model, $data);
    }

    /**
     * Caches a model by an additional compound key.
     *
     * This method needs to be overwritten in subclasses to work. However, it is recommended to use
     * cacheModelByCompoundKey instead. So this method primarily is here for backwards compatibility.
     *
     * @param M $model the model to cache
     * @param DatabaseRow $data the data of the model as it is in the DB, may be empty
     */
    protected function cacheModelByCombinedKeys(AbstractModel $model, array $data): void {}

    /**
     * Looks up a model by key.
     *
     * This function will first check the cache-by-key and, if there is no match,
     * will try to find the model in the database.
     *
     * @param non-empty-string $key an existing key
     * @param non-empty-string $value the value for the key of the model to find
     *
     * @return M the cached model
     *
     * @throws NotFoundException if there is no match (neither in the cache nor in the database)
     *
     * @deprecated #1992 will be removed in version 7.0
     */
    public function findOneByKey(string $key, string $value): AbstractModel
    {
        try {
            $model = $this->findOneByKeyFromCache($key, $value);
        } catch (NotFoundException $notFoundException) {
            $model = $this->findSingleByWhereClause([$key => $value]);
        }

        return $model;
    }

    /**
     * Finds all records that are related to $model via the field $key.
     *
     * @param AbstractModel $model the model to which the matches should be related
     * @param non-empty-string $relationKey the key of the field in the matches that should contain the UID of $model
     * @param Collection<AbstractModel>|null $ignoreList related records that should _not_ be returned
     *
     * @return Collection<M> the related models, will be empty if there are no matches
     */
    public function findAllByRelation(
        AbstractModel $model,
        string $relationKey,
        ?Collection $ignoreList = null,
    ): Collection {
        if (!$model->hasUid()) {
            throw new \InvalidArgumentException('$model must have a UID.', 1_331_319_915);
        }

        // @phpstan-ignore-next-line We are explicitly testing for a contract violation here.
        if ($relationKey === '') {
            throw new \InvalidArgumentException('$relationKey must not be empty.', 1_331_319_921);
        }

        $query = $this->getQueryBuilder();
        $query->select('*')->from($this->getTableName());
        $query->andWhere($query->expr()->eq($relationKey, $query->createNamedParameter($model->getUid())));
        if ($ignoreList instanceof Collection && $ignoreList->getUids() !== '') {
            $query->andWhere(
                $query->expr()->notIn('uid', GeneralUtility::intExplode(',', $ignoreList->getUids(), true)),
            );
        }

        $modelData = $query->executeQuery()->fetchAllAssociative();

        return $this->getListOfModels($modelData);
    }

    /**
     * Returns the table name of this mapper.
     *
     * @return non-empty-string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * Returns the TCA for a certain table.
     *
     * @param non-empty-string $tableName the table name to look up
     *
     * @return array<string, array<string, array<string, array<string, string>>>> TCA description for this table
     */
    protected function getTcaForTable(string $tableName): array
    {
        if (!isset($GLOBALS['TCA'][$tableName])) {
            throw new \BadMethodCallException('The table "' . $tableName . '" has no TCA.', 1_565_462_958);
        }

        return $GLOBALS['TCA'][$tableName];
    }

    protected function getConnection(): Connection
    {
        return $this->getConnectionForTable($this->getTableName());
    }

    /**
     * @param non-empty-string $tableName
     */
    protected function getConnectionForTable(string $tableName): Connection
    {
        return $this->getConnectionPool()->getConnectionForTable($tableName);
    }

    protected function getQueryBuilder(): QueryBuilder
    {
        return $this->getConnectionPool()->getQueryBuilderForTable($this->getTableName());
    }

    /**
     * @param non-empty-string $tableName
     */
    protected function getQueryBuilderForTable(string $tableName): QueryBuilder
    {
        return $this->getConnectionPool()->getQueryBuilderForTable($tableName);
    }

    private function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }

    /**
     * @param non-empty-string $key
     *
     * @return AbstractDataMapper<AbstractModel>
     */
    private function getRelationMapperByKey(string $key): AbstractDataMapper
    {
        $mapperRegistry = GeneralUtility::makeInstance(MapperRegistry::class);

        return $mapperRegistry->getByClassName($this->relations[$key]);
    }
}
