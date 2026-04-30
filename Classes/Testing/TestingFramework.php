<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Testing;

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Mapper\FrontEndUserMapper;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Model\FrontEndUserGroup;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\NullLogger;
use TYPO3\CMS\Core\Configuration\SiteConfiguration;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\ServerRequestFactory;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Localization\Locales;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\TypoScript\AST\Node\RootNode;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Core\TypoScript\IncludeTree\SysTemplateRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * This class provides various functions to handle dummy records in unit tests.
 */
final class TestingFramework
{
    /**
     * all system table names to which this instance of the testing framework
     * has access
     */
    private const ALLOWED_SYSTEM_TABLES = [
        'fe_groups',
        'fe_users',
        'pages',
        'sys_template',
        'tt_content',
        'be_groups',
        'sys_file',
        'sys_file_collection',
        'sys_file_reference',
        'sys_category',
        'sys_category_record_mm',
    ];

    private const FAKE_FRONTEND_DOMAIN_NAME = 'typo3-test.dev';
    private const SITE_IDENTIFIER = 'testing-framework';

    private bool $databaseInitialized = false;

    /**
     * prefix of the extension for which this instance of the testing framework
     * was instantiated (e.g. "tx_seminars")
     *
     * @var non-empty-string|null
     */
    private ?string $tablePrefix;

    /**
     * all own DB table names to which this instance of the testing framework has access,
     * or `null` if there is no restriction on the allowed tables
     *
     * @var list<non-empty-string>|null
     */
    private ?array $ownAllowedTables = null;

    /**
     * sorting values of all relation tables
     *
     * @var array<non-empty-string, array<positive-int, int<0, max>>>
     */
    private array $relationSorting = [];

    /**
     * whether a fake front end has been created
     */
    private bool $hasFakeFrontEnd = false;

    /**
     * hook objects for this class
     *
     * @var list<object>
     */
    private static $hooks = [];

    /**
     * whether the hooks in self::hooks have been retrieved
     */
    private static bool $hooksHaveBeenRetrieved = false;

    /**
     * This testing framework can be instantiated for one extension at a time.
     * Example: In your testcase, you'll have something similar to this line of code:
     *
     * `$this->subject = new TestingFramework('tx_seminars');`
     *
     * The parameter you provide is the prefix of the table names of that particular
     * extension. Like this, we ensure that the testing framework creates and
     * deletes records only on table with this prefix.
     *
     * If you need dummy records on tables of multiple extensions, you will have to
     * instantiate the testing framework multiple times (once per extension).
     *
     * Instantiating this class sets all core caches in order to avoid errors about not registered caches.
     *
     * @param non-empty-string|null $tablePrefix deprecated #2225 will be removed in oelib 7.0;
     *        table name prefix of the extension for this instance of the testing framework
     */
    public function __construct(?string $tablePrefix = null)
    {
        $this->tablePrefix = $tablePrefix;

        GeneralUtility::makeInstance(CacheNullifyer::class)->setAllCoreCaches();
    }

    private function initializeDatabase(): void
    {
        if ($this->databaseInitialized) {
            return;
        }

        $this->createListOfOwnAllowedTables();

        $this->databaseInitialized = true;
    }

    /**
     * Creates a new dummy record for unit tests.
     *
     * If no record data for the new array is given, an empty record will be
     * created. It will only contain a valid UID.
     *
     * Should there be any problem creating the record (wrong table name or a
     * problem with the database), 0 instead of a valid UID will be returned.
     *
     * @param non-empty-string $table the name of the table on which the record should be created
     * @param array<string, string|int|bool> $recordData data to save in the new record, may be empty,
     *        but must not contain the key "uid"
     *
     * @return positive-int the UID of the new record
     *
     * @throws \InvalidArgumentException
     */
    public function createRecord(string $table, array $recordData = []): int
    {
        $this->initializeDatabase();
        if (!$this->isNoneSystemTableNameAllowed($table)) {
            \assert(\is_array($this->ownAllowedTables));
            $allowedTables = \implode(',', $this->ownAllowedTables);
            $errorMessage = \sprintf('The table "%1$s" is not allowed. Allowed tables: %2$s', $table, $allowedTables);
            throw new \InvalidArgumentException($errorMessage, 1_331_489_666);
        }

        if (isset($recordData['uid'])) {
            throw new \InvalidArgumentException('The column "uid" must not be set in $recordData.', 1_331_489_678);
        }

        return $this->createRecordWithoutTableNameChecks($table, $recordData);
    }

    /**
     * Creates a new dummy record for unit tests without checks for the table name.
     *
     * If no record data for the new array is given, an empty record will be created.
     * It will only contain a valid UID.
     *
     * Should there be any problem creating the record (wrong table name or a
     * problem with the database), 0 instead of a valid UID will be returned.
     *
     * @param non-empty-string $table the name of the table on which the record should be created
     * @param array<string, string|int|bool> $rawData data to save, may be empty, but must not contain the key "uid"
     *
     * @return positive-int the UID of the new record
     */
    private function createRecordWithoutTableNameChecks(string $table, array $rawData): int
    {
        $this->initializeDatabase();
        $dataToInsert = $this->normalizeDatabaseRow($rawData);

        $connection = $this->getConnectionForTable($table);
        $connection->insert($table, $dataToInsert);

        $uid = (int)$connection->lastInsertId($table);
        \assert($uid > 0);

        return $uid;
    }

    /**
     * Normalizes the types in the given data so that the data con be inserted into a DB.
     *
     * @param array<string, string|int|bool|float> $rawData
     *
     * @return array<string, string|int|float>
     */
    private function normalizeDatabaseRow(array $rawData): array
    {
        $dataToInsert = [];
        foreach ($rawData as $key => $value) {
            $dataToInsert[$key] = \is_bool($value) ? (int)$value : $value;
        }
        if ($dataToInsert === []) {
            $dataToInsert['pid'] = 0;
        }

        return $dataToInsert;
    }

    /**
     * @param non-empty-string $tableName
     */
    private function getConnectionForTable(string $tableName): Connection
    {
        return $this->getConnectionPool()->getConnectionForTable($tableName);
    }

    private function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }

    /**
     * Creates a front-end page on the given page, and provides it with the page UID as slug.
     *
     * @param array<string, string|int>|null $data
     *
     * @return positive-int the UID of the new page
     */
    public function createFrontEndPage(int $parentPageUid = 0, ?array $data = null): int
    {
        $data ??= [];
        $hasSlug = \array_key_exists('slug', $data);
        $uid = $this->createGeneralPageRecord(1, $parentPageUid, $data);
        if (!$hasSlug) {
            $this->changeRecord('pages', $uid, ['slug' => '/' . $uid]);
        }

        return $uid;
    }

    /**
     * Creates a system folder on the page with the UID given by the first
     * parameter $parentId.
     *
     * @param int $parentId
     *        UID of the page on which the system folder should be created
     *
     * @return positive-int the UID of the new system folder
     */
    public function createSystemFolder(int $parentId = 0): int
    {
        return $this->createGeneralPageRecord(254, $parentId, []);
    }

    /**
     * Creates a page record with the document type given by the first parameter
     * $documentType.
     *
     * The record will be created on the page with the UID given by the second
     * parameter $parentId.
     *
     * @param positive-int $documentType document type of the record to create
     * @param int $parentId UID of the page on which the record should be created
     * @param array<string, string|int|bool> $recordData data to save in the record, may be empty,
     *        but must not contain the keys "uid", "pid" or "doktype"
     *
     * @return positive-int the UID of the new record
     *
     * @throws \InvalidArgumentException
     */
    private function createGeneralPageRecord(int $documentType, int $parentId, array $recordData): int
    {
        if (isset($recordData['uid'])) {
            throw new \InvalidArgumentException('The column "uid" must not be set in $recordData.', 1_331_489_697);
        }

        if (isset($recordData['pid'])) {
            throw new \InvalidArgumentException('The column "pid" must not be set in $recordData.', 1_331_489_703);
        }

        if (isset($recordData['doktype'])) {
            throw new \InvalidArgumentException('The column "doktype" must not be set in $recordData.', 1_331_489_708);
        }

        $completeRecordData = $recordData;
        $completeRecordData['pid'] = $parentId;
        $completeRecordData['doktype'] = $documentType;

        return $this->createRecordWithoutTableNameChecks('pages', $completeRecordData);
    }

    /**
     * Creates a template on the page with the UID given by the first parameter $pageId.
     *
     * @param positive-int $pageId UID of the page on which the template should be created
     * @param array<string, string|int|bool> $recordData data to save, may be empty,
     *        but must not contain the keys "uid" or "pid"
     *
     * @return positive-int the UID of the new template
     *
     * @throws \InvalidArgumentException
     *
     * @deprecated #2197 will be removed in version 7.0
     */
    public function createTemplate(int $pageId, array $recordData = []): int
    {
        // @phpstan-ignore-next-line We're testing for a contract violation here.
        if ($pageId <= 0) {
            throw new \InvalidArgumentException('$pageId must be > 0.', 1_331_489_774);
        }

        if (isset($recordData['uid'])) {
            throw new \InvalidArgumentException('The column "uid" must not be set in $recordData.', 1_331_489_769);
        }

        if (isset($recordData['pid'])) {
            throw new \InvalidArgumentException('The column "pid" must not be set in $recordData.', 1_331_489_764);
        }

        $completeRecordData = $recordData;
        $completeRecordData['pid'] = $pageId;

        return $this->createRecordWithoutTableNameChecks('sys_template', $completeRecordData);
    }

    /**
     * Creates a FE user group.
     *
     * @param array<string, string|int|bool> $recordData data to save, may be empty, but must not contain the key "uid"
     *
     * @return positive-int the UID of the new user group
     *
     * @throws \InvalidArgumentException
     */
    public function createFrontEndUserGroup(array $recordData = []): int
    {
        if (isset($recordData['uid'])) {
            throw new \InvalidArgumentException('The column "uid" must not be set in $recordData.', 1_331_489_807);
        }

        return $this->createRecordWithoutTableNameChecks('fe_groups', $recordData);
    }

    /**
     * Creates a FE user record.
     *
     * @param string|int $frontEndUserGroups
     *        comma-separated list of UIDs of the user groups to which the new user belongs, each must be > 0,
     *        may contain spaces, if empty a new FE user group will be created
     * @param array<string, string|int|bool> $recordData data to save, may be empty,
     *        but must not contain the keys "uid" or "usergroup"
     *
     * @return positive-int the UID of the new FE user
     *
     * @throws \InvalidArgumentException
     */
    public function createFrontEndUser($frontEndUserGroups = '', array $recordData = []): int
    {
        $frontEndUserGroupsWithoutSpaces = str_replace(' ', '', (string)$frontEndUserGroups);

        if ($frontEndUserGroupsWithoutSpaces === '') {
            $frontEndUserGroupsWithoutSpaces = (string)$this->createFrontEndUserGroup();
        }

        $groupsCheckResult = \preg_match('/^(?:[1-9]+\\d*,?)+$/', $frontEndUserGroupsWithoutSpaces);
        if (!\is_int($groupsCheckResult) || $groupsCheckResult === 0) {
            throw new \InvalidArgumentException(
                '$frontEndUserGroups must contain a comma-separated list of UIDs. Each UID must be > 0.',
                1_331_489_824,
            );
        }

        if (isset($recordData['uid'])) {
            throw new \InvalidArgumentException('The column "uid" must not be set in $recordData.', 1_331_489_842);
        }

        if (isset($recordData['usergroup'])) {
            throw new \InvalidArgumentException(
                'The column "usergroup" must not be set in $recordData.',
                1_331_489_846,
            );
        }

        $completeRecordData = $recordData;
        $completeRecordData['usergroup'] = $frontEndUserGroupsWithoutSpaces;

        return $this->createRecordWithoutTableNameChecks('fe_users', $completeRecordData);
    }

    /**
     * Creates and logs in an FE user.
     *
     * @param string|int $frontEndUserGroups comma-separated list of UIDs of the user groups to which the user belongs,
     *        each must be > 0, may contain spaces; if empty a new front-end user group is created
     * @param array<string, string|int|bool> $recordData data to save, may be empty,
     *        but must not contain the keys "uid" or "usergroup"
     *
     * @return positive-int the UID of the new FE user
     */
    public function createAndLoginFrontEndUser($frontEndUserGroups = '', array $recordData = []): int
    {
        $frontEndUserUid = $this->createFrontEndUser($frontEndUserGroups, $recordData);

        $this->loginFrontEndUser($frontEndUserUid);

        return $frontEndUserUid;
    }

    /**
     * Changes an existing dummy record and stores the new data for this
     * record. Only fields that get new values in $recordData will be changed,
     * everything else will stay untouched.
     *
     * The array with the new recordData must contain at least one entry, but
     * must not contain a new UID for the record. If you need to change the UID,
     * you have to create a new record!
     *
     * @param non-empty-string $table the name of the table
     * @param positive-int $uid the UID of the record to change
     * @param non-empty-array<string, string|int|bool|float> $rawData the values to be changed as key-value pairs
     *
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function changeRecord(string $table, int $uid, array $rawData): void
    {
        $this->initializeDatabase();
        $this->assertTableNameIsAllowed($table);
        // @phpstan-ignore-next-line We're testing for a contract violation here.
        if ($uid === 0) {
            throw new \InvalidArgumentException('The parameter $uid must not be zero.', 1_331_490_003);
        }

        // @phpstan-ignore-next-line We're testing for a contract violation here.
        if ($rawData === []) {
            throw new \InvalidArgumentException('The array with the new record data must not be empty.', 1_331_490_008);
        }

        if (isset($rawData['uid'])) {
            throw new \InvalidArgumentException(
                'The parameter $recordData must not contain changes to the UID of a record.',
                1_331_490_017,
            );
        }

        $dataToSave = $this->normalizeDatabaseRow($rawData);
        $this->getConnectionForTable($table)->update($table, $dataToSave, ['uid' => $uid]);
    }

    /**
     * Creates a relation between two records on different tables (so called
     * m:n relation).
     *
     * @param non-empty-string $table name of the m:n table to which the record should be added
     * @param positive-int $uidLocal UID of the local table
     * @param positive-int $uidForeign UID of the foreign table
     *
     * @throws \InvalidArgumentException
     */
    public function createRelation(string $table, int $uidLocal, int $uidForeign): void
    {
        $this->initializeDatabase();

        // @phpstan-ignore-next-line We're testing for a contract violation here.
        if ($uidLocal <= 0) {
            throw new \InvalidArgumentException('$uidLocal must be > 0, but is: ' . $uidLocal, 1_331_490_370);
        }

        // @phpstan-ignore-next-line We're testing for a contract violation here.
        if ($uidForeign <= 0) {
            throw new \InvalidArgumentException('$uidForeign must be > 0, but is: ' . $uidForeign, 1_331_490_378);
        }

        $recordData = [
            'uid_local' => $uidLocal,
            'uid_foreign' => $uidForeign,
            'sorting' => $this->getRelationSorting($table, $uidLocal),
        ];

        $this->getConnectionForTable($table)->insert($table, $recordData);
    }

    /**
     * Creates a relation between two records based on the rules defined in TCA
     * regarding the relation.
     *
     * @param non-empty-string $tableName name of the table from which a relation should be created
     * @param positive-int $uidLocal UID of the record in the local table
     * @param positive-int $uidForeign UID of the record in the foreign table
     * @param non-empty-string $columnName name of the column in which the relation counter should be updated
     *
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function createRelationAndUpdateCounter(
        string $tableName,
        int $uidLocal,
        int $uidForeign,
        string $columnName
    ): void {
        $this->initializeDatabase();
        $this->assertTableNameIsAllowed($tableName);
        // @phpstan-ignore-next-line We're testing for a contract violation here.
        if ($uidLocal <= 0) {
            throw new \InvalidArgumentException(
                '$uidLocal must be > 0, but actually is "' . $uidLocal . '"',
                1_331_490_425,
            );
        }

        // @phpstan-ignore-next-line We're testing for a contract violation here.
        if ($uidForeign <= 0) {
            throw new \InvalidArgumentException(
                '$uidForeign must be  > 0, but actually is "' . $uidForeign . '"',
                1_331_490_429,
            );
        }

        $tca = $this->getTcaForTable($tableName);
        $relationConfiguration = $tca['columns'][$columnName];

        if (!isset($relationConfiguration['config']['MM']) || ($relationConfiguration['config']['MM'] === '')) {
            throw new \BadMethodCallException(
                'The column ' . $columnName . ' in the table ' . $tableName .
                ' is not configured to contain m:n relations using a m:n table.',
                1_331_490_434,
            );
        }

        if (isset($relationConfiguration['config']['MM_opposite_field'])) {
            // Switches the order of $uidForeign and $uidLocal as the relation
            // is the reverse part of a bidirectional relation.
            $this->createRelationAndUpdateCounter(
                $relationConfiguration['config']['foreign_table'],
                $uidForeign,
                $uidLocal,
                $relationConfiguration['config']['MM_opposite_field'],
            );
        } else {
            $this->createRelation(
                $relationConfiguration['config']['MM'],
                $uidLocal,
                $uidForeign,
            );
        }

        $this->increaseRelationCounter($tableName, $uidLocal, $columnName);
    }

    /**
     * Returns the TCA for a certain table.
     *
     * @param non-empty-string $tableName the table name to look up
     *
     * @return array<array<string, mixed>> associative array with the TCA description for this table
     */
    private function getTcaForTable(string $tableName): array
    {
        if (!isset($GLOBALS['TCA'][$tableName])) {
            throw new \BadMethodCallException('The table "' . $tableName . '" has no TCA.', 1_569_701_919);
        }

        return $GLOBALS['TCA'][$tableName];
    }

    /**
     * Cleans up.
     */
    public function cleanUpWithoutDatabase(): void
    {
        $this->discardFakeFrontEnd();
        WritableEnvironment::restoreCurrentScript();
        GeneralUtility::flushInternalRuntimeCaches();

        // @deprecated #2216 will be removed in oelib 7.0
        foreach ($this->getHooks() as $hook) {
            if (method_exists($hook, 'cleanUp')) {
                $hook->cleanUp($this);
            }
        }

        if ((new Typo3Version())->getMajorVersion() <= 11) {
            RootlineUtility::purgeCaches();
        }
    }

    // Functions concerning a fake front end

    /**
     * @return non-empty-string
     */
    public function getFakeFrontEndDomain(): string
    {
        return self::FAKE_FRONTEND_DOMAIN_NAME;
    }

    /**
     * @return non-empty-string
     */
    public function getFakeSiteUrl(): string
    {
        return 'http://' . $this->getFakeFrontEndDomain() . '/';
    }

    /**
     * Fakes a TYPO3 front end, using `$pageUid` as front-end page ID.
     *
     * This function creates `$GLOBALS['TSFE']`.
     *
     * @param positive-int $pageUid
     *
     * @return positive-int the UID of the used front-end page, deprecated #2279, will be `void` in oelib 7.0
     *
     * @throws \InvalidArgumentException if $pageUid is < 0
     */
    public function createFakeFrontEnd(int $pageUid): int
    {
        /** @phpstan-ignore-next-line We are explicitly checking for contract violations here */
        if ($pageUid <= 0) {
            throw new \InvalidArgumentException('$pageUid must be > 0.', 1_331_490_786);
        }

        $this->discardFakeFrontEnd();

        $this->setServerGlobalsForFakeFrontEnd($pageUid);
        $this->setPageIndependentGlobalsForFakeFrontEnd();

        $frontEndUser = GeneralUtility::makeInstance(FrontendUserAuthentication::class);
        $request = ServerRequestFactory::fromGlobals();

        $frontEndUser->start($request);
        $frontEndUser->fetchGroupData($request);
        if ((new Typo3Version())->getMajorVersion() <= 11) {
            $frontEndUser->unpack_uc();
        }

        $this->createDummySite($pageUid);
        $allSites = GeneralUtility::makeInstance(SiteConfiguration::class)->getAllExistingSites(false);
        $site = $allSites[self::SITE_IDENTIFIER] ?? null;
        if (!$site instanceof Site) {
            throw new \RuntimeException('Dummy site not found.', 1_635_024_025);
        }

        $language = $site->getLanguageById(0);
        $pageArguments = new PageArguments($pageUid, '', []);
        $frontEndController = GeneralUtility::makeInstance(
            TypoScriptFrontendController::class,
            GeneralUtility::makeInstance(Context::class),
            $site,
            $language,
            $pageArguments,
            $frontEndUser,
        );
        $frontEndController->set_no_cache('fake frontend', true);
        $rootlineUtility = GeneralUtility::makeInstance(RootlineUtility::class, $pageUid);
        $rootline = $rootlineUtility->get();
        $frontEndController->rootLine = $rootline;
        $GLOBALS['TSFE'] = $frontEndController;

        $frontEndController->fe_user = $frontEndUser;
        $frontEndController->id = $pageUid;
        if ((new Typo3Version())->getMajorVersion() <= 12) {
            $frontEndController->determineId($request);
        }
        $frontEndController->config = [
            'config' => ['MP_disableTypolinkClosestMPvalue' => true, 'typolinkLinkAccessRestrictedPages' => true],
        ];

        Locales::setSystemLocaleFromSiteLanguage($language);

        if ((new Typo3Version())->getMajorVersion() >= 13) {
            $frontEndController->newCObj($request);
        } else {
            $frontEndController->newCObj();
        }
        $contentObject = $frontEndController->cObj;
        \assert($contentObject instanceof ContentObjectRenderer);
        $contentObject->setLogger(new NullLogger());

        $request = $request
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE)
            ->withAttribute('frontend.controller', $frontEndController)
            ->withAttribute('frontend.user', $frontEndUser)
            ->withAttribute('language', $language)
            ->withAttribute('normalizedParams', NormalizedParams::createFromRequest($request))
            ->withAttribute('routing', $pageArguments)
            ->withAttribute('site', $site);
        if ((new Typo3Version())->getMajorVersion() >= 12) {
            $request = $request->withAttribute('currentContentObject', $contentObject);
            $request = $this->addTypoScriptToRequest($frontEndController, $rootline, $request);
        }
        $GLOBALS['TYPO3_REQUEST'] = $request;
        $contentObject->setRequest($request);

        $this->hasFakeFrontEnd = true;
        $this->logoutFrontEndUser();

        return $pageUid;
    }

    /**
     * Discards all site configuration files, and creates a new configuration file for a dummy site.
     *
     * Starting with TYPO3 10, we will be able to use `SiteConfiguration::createNewBasicSite()` for this.
     */
    private function createDummySite(int $pageUid): void
    {
        $siteConfigurationDirectory = Environment::getConfigPath() . '/sites/';
        GeneralUtility::rmdir($siteConfigurationDirectory, true);
        $configurationDirectoryForTestingDummySite = $siteConfigurationDirectory . self::SITE_IDENTIFIER;
        GeneralUtility::mkdir_deep($configurationDirectoryForTestingDummySite);

        $url = $this->getFakeSiteUrl();
        $contents =
            "rootPageId: {$pageUid}
base: '{$url}'
baseVariants: {  }
languages:
  -
    title: 'Englisch'
    enabled: true
    languageId: 0
    base: '/'
    typo3Language: 'default'
    locale: 'en_US.UTF-8'
    iso-639-1: 'en'
    navigationTitle: 'Englisch'
    hreflang: 'en-US'
    direction: 'ltr'
    flag: 'us'
errorHandling: {  }
routes: {  }";

        $file = $configurationDirectoryForTestingDummySite . '/config.yaml';
        \file_put_contents($file, $contents);
        if (!\is_readable($file)) {
            throw new \RuntimeException('Site config file "' . $file . '" could not be created.', 1_634_918_114);
        }
    }

    /**
     * @param int<1, max> $pageUid
     */
    private function setServerGlobalsForFakeFrontEnd(int $pageUid): void
    {
        $documentRoot = '/var/www/html/public';
        $relativeScriptPath = '/index.php';
        $absoluteScriptPath = $documentRoot . '/index.php';
        $slug = '/' . $pageUid;

        $_SERVER['DOCUMENT_ROOT'] = $documentRoot;
        $_SERVER['HTTP_HOST'] = $this->getFakeFrontEndDomain();
        $_SERVER['QUERY_STRING'] = '';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['REMOTE_HOST'] = '';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = $slug;
        $_SERVER['SCRIPT_FILENAME'] = $absoluteScriptPath;
        $_SERVER['SCRIPT_NAME'] = $relativeScriptPath;
    }

    private function setPageIndependentGlobalsForFakeFrontEnd(): void
    {
        GeneralUtility::flushInternalRuntimeCaches();
        unset($GLOBALS['TYPO3_REQUEST']);

        $hostName = $this->getFakeFrontEndDomain();
        $documentRoot = '/var/www/html/public';
        $relativeScriptPath = '/index.php';
        $absoluteScriptPath = $documentRoot . '/index.php';

        GeneralUtility::setIndpEnv('DOCUMENT_ROOT', $documentRoot);
        GeneralUtility::setIndpEnv('HOSTNAME', $hostName);
        GeneralUtility::setIndpEnv('HTTP', 'off');
        GeneralUtility::setIndpEnv('HTTP_ACCEPT_ENCODING', 'gzip, deflate, br');
        GeneralUtility::setIndpEnv('HTTP_ACCEPT_LANGUAGE', 'de,en-US;q=0.7,en;q=0.3');
        GeneralUtility::setIndpEnv('HTTP_HOST', $hostName);
        GeneralUtility::setIndpEnv('HTTP_REFERER', $this->getFakeSiteUrl());
        GeneralUtility::setIndpEnv(
            'HTTP_USER_AGENT',
            'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:121.0) Gecko/20100101 Firefox/121.0',
        );
        GeneralUtility::setIndpEnv('PHP_SELF', '/index.php');
        GeneralUtility::setIndpEnv('QUERY_STRING', '');
        GeneralUtility::setIndpEnv('REMOTE_ADDR', '127.0.0.1');
        GeneralUtility::setIndpEnv('REMOTE_HOST', '');
        GeneralUtility::setIndpEnv('REQUEST_SCHEME', 'http');
        GeneralUtility::setIndpEnv('SCRIPT_FILENAME', $absoluteScriptPath);
        GeneralUtility::setIndpEnv('SCRIPT_NAME', $relativeScriptPath);
        GeneralUtility::setIndpEnv('SERVER_ADDR', '127.0.0.1');
        GeneralUtility::setIndpEnv('SERVER_NAME', $hostName);
        GeneralUtility::setIndpEnv('SERVER_SOFTWARE', 'Apache/2.4.48 (Debian)');

        WritableEnvironment::setCurrentScript($absoluteScriptPath);
    }

    /**
     * Discards the fake front end.
     *
     * This function nulls out $GLOBALS['TSFE']. In addition, any logged-in front-end user will be logged out.
     *
     * The page record for the current front end will _not_ be deleted by this
     * function, though.
     *
     * If no fake front end has been created, this function does nothing.
     */
    private function discardFakeFrontEnd(): void
    {
        if (!$this->hasFakeFrontEnd()) {
            return;
        }

        $this->logoutFrontEndUser();

        $GLOBALS['TSFE'] = null;
        unset(
            $GLOBALS['TYPO3_REQUEST'],
            $GLOBALS['TYPO3_CONF_VARS']['FE']['dontSetCookie'],
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][FrontendUserAuthentication::class],
        );

        $this->hasFakeFrontEnd = false;
    }

    /**
     * Returns whether this testing framework instance has a fake front end.
     *
     * @return bool TRUE if this instance has a fake front end, FALSE
     *                 otherwise
     */
    private function hasFakeFrontEnd(): bool
    {
        return $this->hasFakeFrontEnd;
    }

    // FE user activities

    /**
     * Fakes that a front-end user has logged in.
     *
     * If a front-end user currently is logged in, he/she will be logged out
     * first.
     *
     * Note: To set the logged-in users group data properly, the front-end user
     *       and his groups must actually exist in the database.
     *
     * @param positive-int $userId UID of the FE user, must not necessarily exist in the database
     *
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException if no front end has been created
     */
    private function loginFrontEndUser(int $userId): void
    {
        // @phpstan-ignore-next-line We're testing for a contract violation here.
        if ($userId <= 0) {
            throw new \InvalidArgumentException('The user ID must be > 0.', 1_331_490_798);
        }

        if (!$this->hasFakeFrontEnd()) {
            throw new \BadMethodCallException(
                'Please create a front end before calling loginFrontEndUser.',
                1_331_490_812,
            );
        }

        if ($this->isLoggedIn()) {
            $this->logoutFrontEndUser();
        }

        $mapper = MapperRegistry::get(FrontEndUserMapper::class);
        // loads the model from database if it is a ghost
        $mapper->existsModel($userId);

        $dataToSet = $mapper->find($userId)->getData();
        $dataToSet['uid'] = $userId;
        if (isset($dataToSet['usergroup'])) {
            /** @var Collection<FrontEndUserGroup> $userGroups */
            $userGroups = $dataToSet['usergroup'];
            $dataToSet['usergroup'] = $userGroups->getUids();
        }

        $frontEndUser = $this->getFrontEndController()->fe_user;
        $frontEndUser->createUserSession(['uid' => $userId, 'disableIPlock' => true]);

        $frontEndUser->user = $dataToSet;
        $frontEndUser->fetchGroupData(new ServerRequest());

        GeneralUtility::makeInstance(Context::class)->setAspect('frontend.user', new UserAspect($frontEndUser));
    }

    /**
     * Logs out the current front-end user.
     *
     * If no front-end user is logged in, this function does nothing.
     *
     * @throws \BadMethodCallException if no front end has been created
     *
     * @deprecated #2219 will be removed in oelib 7.0
     */
    public function logoutFrontEndUser(): void
    {
        if (!$this->hasFakeFrontEnd()) {
            throw new \BadMethodCallException(
                'Please create a front end before calling logoutFrontEndUser.',
                1_331_490_825,
            );
        }

        if (!$this->isLoggedIn()) {
            return;
        }

        $frontEndUser = $this->getFrontEndController()->fe_user;
        $frontEndUser->logoff();

        GeneralUtility::makeInstance(Context::class)->setAspect('frontend.user', new UserAspect());
    }

    /**
     * Checks whether a FE user is logged in.
     *
     * @return bool TRUE if a FE user is logged in, FALSE otherwise
     *
     * @throws \BadMethodCallException if no front end has been created
     *
     * @internal
     */
    private function isLoggedIn(): bool
    {
        if (!$this->hasFakeFrontEnd()) {
            throw new \BadMethodCallException('Please create a front end before calling isLoggedIn.', 1_331_490_846);
        }

        return (bool)GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('frontend.user', 'isLoggedIn');
    }

    // ----------------------------------------------------------------------
    // Various helper functions
    // ----------------------------------------------------------------------

    /**
     * Returns a list of all table names that are available in the current
     * database.
     *
     * @return list<non-empty-string> table names
     */
    private function getAllTableNames(): array
    {
        \assert(isset($GLOBALS['TCA']) && is_array($GLOBALS['TCA']));

        /** @var list<non-empty-string> $tableNames */
        $tableNames = \array_keys($GLOBALS['TCA']);

        return $tableNames;
    }

    /**
     * Generates a list of allowed tables to which this instance of the testing
     * framework has access to create/remove test records.
     *
     * The generated list is based on the list of all tables that TYPO3 can
     * access (which will be all tables in this database), filtered by prefix of
     * the extension to test.
     *
     * The array with the allowed table names is written directly to
     * `$this->ownAllowedTables`.
     */
    private function createListOfOwnAllowedTables(): void
    {
        if (!\is_string($this->tablePrefix)) {
            return;
        }

        $this->ownAllowedTables = [];
        $allTables = $this->getAllTableNames();
        $length = \strlen($this->tablePrefix);

        foreach ($allTables as $currentTable) {
            if (substr_compare($this->tablePrefix, $currentTable, 0, $length) === 0) {
                $this->ownAllowedTables[] = $currentTable;
            }
        }
    }

    /**
     * Checks whether the given table name is in the list of allowed tables for
     * this instance of the testing framework (or whether all tables are allowed).
     */
    private function isOwnTableNameAllowed(string $table): bool
    {
        if (!\is_array($this->ownAllowedTables)) {
            return true;
        }

        return \in_array($table, $this->ownAllowedTables, true);
    }

    /**
     * Checks whether the given table name is in the list of allowed
     * system tables for this instance of the testing framework.
     */
    private function isSystemTableNameAllowed(string $table): bool
    {
        return \in_array($table, self::ALLOWED_SYSTEM_TABLES, true);
    }

    /**
     * Checks whether the given table name is in the list of allowed tables for this instance of the testing framework.
     *
     * @param string $table the name of the table to check
     */
    private function isNoneSystemTableNameAllowed(string $table): bool
    {
        return $this->isOwnTableNameAllowed($table);
    }

    /**
     * Checks whether the given table name is in the list of allowed tables or allowed system tables,
     * and throws an exception if it is not.
     *
     * @throws \InvalidArgumentException
     */
    private function assertTableNameIsAllowed(string $table): void
    {
        $isAllowed = $this->isNoneSystemTableNameAllowed($table) || $this->isSystemTableNameAllowed($table);
        if (!$isAllowed) {
            \assert(\is_array($this->ownAllowedTables));
            $allowedTables = \implode(',', [...self::ALLOWED_SYSTEM_TABLES, ...$this->ownAllowedTables]);
            $errorMessage = \sprintf('The table "%1$s" is not allowed. Allowed tables: %2$s', $table, $allowedTables);
            throw new \InvalidArgumentException($errorMessage, 1_569_784_847);
        }
    }

    /**
     * Returns the next sorting value of the relation table which should be used.
     *
     * Note: This function does not take already existing relations in the
     * database - which were created without using the testing framework - into
     * account. So you always should create new dummy records and create a
     * relation between these two dummy records, so you're sure there aren't
     * already relations for a local UID in the database.
     *
     * @see https://bugs.oliverklee.com/show_bug.cgi?id=1423
     *
     * @param non-empty-string $table the relation table
     * @param positive-int $uidLocal UID of the local table
     *
     * @return positive-int the next sorting value to use
     */
    private function getRelationSorting(string $table, int $uidLocal): int
    {
        if (!isset($this->relationSorting[$table][$uidLocal])) {
            $this->relationSorting[$table][$uidLocal] = 0;
        }

        ++$this->relationSorting[$table][$uidLocal];

        return $this->relationSorting[$table][$uidLocal];
    }

    /**
     * Updates an integer field of a database table by one. This is mainly needed
     * for counting up the relation counter when creating a database relation.
     *
     * The field to update must be of type int.
     *
     * @param non-empty-string $tableName name of the table
     * @param positive-int $uid the UID of the record to modify
     * @param non-empty-string $fieldName the field name of the field to modify
     *
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    private function increaseRelationCounter(string $tableName, int $uid, string $fieldName): void
    {
        $this->assertTableNameIsAllowed($tableName);

        $connection = $this->getConnectionForTable($tableName);
        $query = 'UPDATE ' . $tableName . ' SET ' . $fieldName . '=' . $fieldName . '+1 WHERE uid=' . $uid;
        $numberOfAffectedRows = $connection->executeStatement($query);
        if ($numberOfAffectedRows === 0) {
            throw new \BadMethodCallException(
                'The table ' . $tableName . ' does not contain a record with UID ' . $uid . '.',
                1_331_491_003,
            );
        }
    }

    /**
     * Gets all hooks for this class.
     *
     * @return list<object> the hook objects, will be empty if no hooks have been set
     *
     * @deprecated #2216 will be removed in oelib 7.0
     */
    private function getHooks(): array
    {
        if (self::$hooksHaveBeenRetrieved) {
            return self::$hooks;
        }

        /** @var array<array-key, class-string> $hookClasses */
        $hookClasses = (array)($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['oelib']['testingFrameworkCleanUp'] ?? []);
        foreach ($hookClasses as $hookClass) {
            self::$hooks[] = GeneralUtility::makeInstance($hookClass);
        }

        self::$hooksHaveBeenRetrieved = true;

        return self::$hooks;
    }

    /**
     * Purges the cached hooks.
     */
    public function purgeHooks(): void
    {
        self::$hooks = [];
        self::$hooksHaveBeenRetrieved = false;
    }

    /**
     * Returns the current front-end instance.
     *
     * This method must only be called when there is a front-end instance.
     */
    private function getFrontEndController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }

    /**
     * @param array<mixed> $rootline
     */
    private function addTypoScriptToRequest(
        TypoScriptFrontendController $controller,
        array $rootline,
        ServerRequest $request
    ): ServerRequestInterface {
        $sysTemplateRepository = GeneralUtility::makeInstance(SysTemplateRepository::class);
        $sysTemplateRows = $sysTemplateRepository->getSysTemplateRowsByRootline($rootline, $request);

        if ($sysTemplateRows !== [] && (new Typo3Version())->getMajorVersion() === 12) {
            $newRequest = $controller->getFromCache($request);
        } else {
            $newRequest = $request->withAttribute('frontend.typoscript', $this->buildEmptyTypoScript());
        }

        return $newRequest;
    }

    private function buildEmptyTypoScript(): FrontendTypoScript
    {
        $typoScript = new FrontendTypoScript(new RootNode(), []);
        $typoScript->setSetupTree(new RootNode());
        $typoScript->setSetupArray([]);

        return $typoScript;
    }
}
