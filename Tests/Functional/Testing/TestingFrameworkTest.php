<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Testing;

use OliverKlee\Oelib\Testing\TestingFramework;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Oelib\Testing\TestingFramework
 * @covers \OliverKlee\Oelib\Testing\TestingFrameworkCleanup
 *
 * @phpstan-type DatabaseColumn string|int|float|bool|null
 * @phpstan-type DatabaseRow array<string, DatabaseColumn>
 */
final class TestingFrameworkTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = ['oliverklee/oelib'];

    private TestingFramework $subject;

    protected function setUp(): void
    {
        $GLOBALS['TSFE'] = null;
        parent::setUp();

        $this->subject = new TestingFramework('tx_oelib');
    }

    protected function tearDown(): void
    {
        $this->subject->cleanUpWithoutDatabase();
        parent::tearDown();
    }

    // Utility functions.

    /**
     * Returns the current front-end instance.
     */
    private function getFrontEndController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }

    private function getContext(): Context
    {
        return GeneralUtility::makeInstance(Context::class);
    }

    /**
     * Returns the sorting value of the relation between the local UID given by
     * the first parameter `$uidLocal` and the foreign UID given by the second
     * parameter `$uidForeign`.
     */
    private function getSortingOfRelation(int $uidLocal, int $uidForeign): int
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_oelib_test_article_mm');
        $result = $connection
            ->select(['*'], 'tx_oelib_test_article_mm', ['uid_local' => $uidLocal, 'uid_foreign' => $uidForeign]);
        /** @var DatabaseRow|false $data */
        $data = $result->fetchAssociative();
        self::assertIsArray($data);

        return (int)$data['sorting'];
    }

    // Tests regarding createRecord()

    /**
     * @test
     */
    public function createRecordOnValidTableWithNoData(): void
    {
        self::assertNotSame(
            0,
            $this->subject->createRecord('tx_oelib_test', []),
        );
    }

    /**
     * @test
     */
    public function createRecordWithValidData(): void
    {
        $title = 'TEST record';
        $uid = $this->subject->createRecord(
            'tx_oelib_test',
            [
                'title' => $title,
            ],
        );
        self::assertNotSame(
            0,
            $uid,
        );

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_oelib_test');
        $result = $connection->select(['*'], 'tx_oelib_test', ['uid' => $uid]);
        /** @var DatabaseRow|false $row */
        $row = $result->fetchAssociative();

        self::assertIsArray($row);
        self::assertSame($title, $row['title']);
    }

    /**
     * @test
     */
    public function createRecordOnInvalidTable(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The table "tx_oelib_DOESNOTEXIST" is not allowed.');
        $this->subject->createRecord('tx_oelib_DOESNOTEXIST', []);
    }

    /**
     * @test
     */
    public function createRecordWithEmptyTableName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The table "" is not allowed.');

        // @phpstan-ignore-next-line We are explicitly testing for a contract violation here.
        $this->subject->createRecord('', []);
    }

    /**
     * @test
     */
    public function createRecordWithUidFails(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The column "uid" must not be set in $recordData.');

        $this->subject->createRecord(
            'tx_oelib_test',
            ['uid' => 99999],
        );
    }

    /**
     * @test
     */
    public function createRecordCanCreateHiddenRecord(): void
    {
        $uid = $this->subject->createRecord('tx_oelib_test', ['hidden' => 1]);

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $query = 'SELECT COUNT(*) as count from tx_oelib_test WHERE uid = :uid AND hidden = :hidden';
        $queryResult = $connection->executeQuery($query, ['uid' => $uid, 'hidden' => 1]);
        $row = $queryResult->fetchAssociative();
        self::assertIsArray($row);
        self::assertSame(1, $row['count']);
    }

    /**
     * @test
     */
    public function createRecordCanCreateDeletedRecord(): void
    {
        $uid = $this->subject->createRecord('tx_oelib_test', ['deleted' => 1]);

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        // We cannot use `$connection->count()` here because it automatically ignores hidden or deleted records.
        $query = 'SELECT COUNT(*) as count from tx_oelib_test WHERE uid = :uid AND deleted = :deleted';
        $queryResult = $connection->executeQuery($query, ['uid' => $uid, 'deleted' => 1]);
        $row = $queryResult->fetchAssociative();
        self::assertIsArray($row);
        self::assertSame(1, $row['count']);
    }

    /**
     * @return bool[][]
     */
    public function booleanDataProvider(): array
    {
        return [
            'false' => [false],
            'true' => [true],
        ];
    }

    /**
     * @test
     *
     * @dataProvider booleanDataProvider
     */
    public function createRecordPersistsBooleansAsIntegers(bool $value): void
    {
        $this->subject->createRecord('tx_oelib_test', ['bool_data1' => $value]);

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        self::assertSame(
            1,
            $connection->count('*', 'tx_oelib_test', ['bool_data1' => (int)$value]),
        );
    }

    // Tests regarding changeRecord()

    /**
     * @test
     */
    public function changeRecordWithExistingRecord(): void
    {
        $uid = $this->subject->createRecord(
            'tx_oelib_test',
            ['title' => 'foo'],
        );

        $this->subject->changeRecord(
            'tx_oelib_test',
            $uid,
            ['title' => 'bar'],
        );

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_oelib_test');
        $result = $connection->select(['*'], 'tx_oelib_test', ['uid' => $uid]);
        /** @var DatabaseRow|false $row */
        $row = $result->fetchAssociative();

        self::assertIsArray($row);
        self::assertSame('bar', $row['title']);
    }

    /**
     * @test
     */
    public function changeRecordFailsOnForeignTable(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The table "tx_seminars_seminars" is not allowed.');
        $this->subject->changeRecord(
            'tx_seminars_seminars',
            99999,
            ['title' => 'foo'],
        );
    }

    /**
     * @test
     */
    public function changeRecordFailsOnInexistentTable(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The table "tx_oelib_DOESNOTEXIST" is not allowed.');
        $this->subject->changeRecord(
            'tx_oelib_DOESNOTEXIST',
            99999,
            ['title' => 'foo'],
        );
    }

    /**
     * @test
     */
    public function changeRecordOnAllowedSystemTableForPages(): void
    {
        $pid = $this->subject->createFrontEndPage();

        $this->subject->changeRecord(
            'pages',
            $pid,
            ['title' => 'bar'],
        );

        $connection = $this->getConnectionPool()->getConnectionForTable('pages');
        self::assertSame(
            1,
            $connection->count('*', 'pages', ['uid' => $pid, 'title' => 'bar']),
        );
    }

    /**
     * @test
     */
    public function changeRecordFailsOnOtherSystemTable(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The table "sys_domain" is not allowed.');
        $this->subject->changeRecord(
            'sys_domain',
            1,
            ['title' => 'bar'],
        );
    }

    /**
     * @test
     */
    public function changeRecordFailsWithUidZero(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The parameter $uid must not be zero.');
        // @phpstan-ignore-next-line We're testing for a contract violation here.
        $this->subject->changeRecord('tx_oelib_test', 0, ['title' => 'foo']);
    }

    /**
     * @test
     */
    public function changeRecordFailsWithEmptyData(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The array with the new record data must not be empty.');
        $uid = $this->subject->createRecord('tx_oelib_test', []);

        // @phpstan-ignore-next-line We are explicitly checking for a contract violation here.
        $this->subject->changeRecord('tx_oelib_test', $uid, []);
    }

    /**
     * @test
     */
    public function changeRecordFailsWithUidFieldInRecordData(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The parameter $recordData must not contain changes to the UID of a record.');
        $uid = $this->subject->createRecord('tx_oelib_test', []);

        $this->subject->changeRecord(
            'tx_oelib_test',
            $uid,
            ['uid' => '55742'],
        );
    }

    /**
     * @test
     *
     * @dataProvider booleanDataProvider
     */
    public function changeRecordPersistsBooleansAsIntegers(bool $value): void
    {
        $uid = $this->subject->createRecord('tx_oelib_test');

        $this->subject->changeRecord('tx_oelib_test', $uid, ['bool_data1' => $value]);

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $count = $connection->count('*', 'tx_oelib_test', ['bool_data1' => (int)$value]);

        self::assertSame(1, $count);
    }

    // Tests regarding createRelation()

    /**
     * @test
     */
    public function createRelationWithValidData(): void
    {
        $uidLocal = $this->subject->createRecord('tx_oelib_test');
        $uidForeign = $this->subject->createRecord('tx_oelib_test');

        $this->subject->createRelation(
            'tx_oelib_test_article_mm',
            $uidLocal,
            $uidForeign,
        );

        // Checks whether the record really exists.
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test_article_mm');
        $count = $relationConnection->count(
            '*',
            'tx_oelib_test_article_mm',
            ['uid_local' => $uidLocal, 'uid_foreign' => $uidForeign],
        );
        self::assertSame(1, $count);
    }

    /**
     * @test
     */
    public function createRelationWithInvalidTable(): void
    {
        $table = 'tx_oelib_test_DOESNOTEXIST_mm';
        $uidLocal = 99999;
        $uidForeign = 199999;

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The table "' . $table . '" is not allowed.');
        $this->subject->createRelation($table, $uidLocal, $uidForeign);
    }

    /**
     * @test
     */
    public function createRelationWithEmptyTableName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The table "" is not allowed.');
        // @phpstan-ignore-next-line We are explicitly testing for a contract violation here.
        $this->subject->createRelation('', 99999, 199999);
    }

    /**
     * @test
     */
    public function createRelationWithZeroFirstUid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$uidLocal must be > 0, but is: 0');

        $uid = $this->subject->createRecord('tx_oelib_test');

        // @phpstan-ignore-next-line We're testing for a contract violation here.
        $this->subject->createRelation('tx_oelib_test_article_mm', 0, $uid);
    }

    /**
     * @test
     */
    public function createRelationWithZeroSecondUid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$uidForeign must be > 0, but is: 0');

        $uid = $this->subject->createRecord('tx_oelib_test');

        // @phpstan-ignore-next-line We're testing for a contract violation here.
        $this->subject->createRelation('tx_oelib_test_article_mm', $uid, 0);
    }

    /**
     * @test
     */
    public function createRelationWithNegativeFirstUid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$uidLocal must be > 0, but is: -1');

        $uid = $this->subject->createRecord('tx_oelib_test');

        // @phpstan-ignore-next-line We're testing for a contract violation here.
        $this->subject->createRelation('tx_oelib_test_article_mm', -1, $uid);
    }

    /**
     * @test
     */
    public function createRelationWithNegativeSecondUid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$uidForeign must be > 0, but is: -1');

        $uid = $this->subject->createRecord('tx_oelib_test');

        // @phpstan-ignore-next-line We're testing for a contract violation here.
        $this->subject->createRelation('tx_oelib_test_article_mm', $uid, -1);
    }

    /**
     * @test
     */
    public function createRelationWithAutomaticSorting(): void
    {
        $uidLocal = $this->subject->createRecord('tx_oelib_test');
        $uidForeign = $this->subject->createRecord('tx_oelib_test');
        $this->subject->createRelation(
            'tx_oelib_test_article_mm',
            $uidLocal,
            $uidForeign,
        );
        $previousSorting = $this->getSortingOfRelation($uidLocal, $uidForeign);
        self::assertGreaterThan(
            0,
            $previousSorting,
        );

        $uidForeign = $this->subject->createRecord('tx_oelib_test');
        $this->subject->createRelation(
            'tx_oelib_test_article_mm',
            $uidLocal,
            $uidForeign,
        );
        $nextSorting = $this->getSortingOfRelation($uidLocal, $uidForeign);
        self::assertSame(
            $previousSorting + 1,
            $nextSorting,
        );
    }

    // Tests regarding createRelationFromTca()

    /**
     * @test
     */
    public function createRelationAndUpdateCounterIncreasesZeroValueCounterByOne(): void
    {
        $firstRecordUid = $this->subject->createRecord('tx_oelib_test');
        $secondRecordUid = $this->subject->createRecord('tx_oelib_test');

        $this->subject->createRelationAndUpdateCounter(
            'tx_oelib_test',
            $firstRecordUid,
            $secondRecordUid,
            'related_records',
        );

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_oelib_test');
        $result = $connection->select(['*'], 'tx_oelib_test', ['uid' => $firstRecordUid]);
        /** @var DatabaseRow|false $row */
        $row = $result->fetchAssociative();

        self::assertIsArray($row);
        self::assertSame(1, (int)$row['related_records']);
    }

    /**
     * @test
     */
    public function createRelationAndUpdateCounterIncreasesNonZeroValueCounterToOne(): void
    {
        $firstRecordUid = $this->subject->createRecord(
            'tx_oelib_test',
            ['related_records' => 1],
        );
        $secondRecordUid = $this->subject->createRecord('tx_oelib_test');

        $this->subject->createRelationAndUpdateCounter(
            'tx_oelib_test',
            $firstRecordUid,
            $secondRecordUid,
            'related_records',
        );

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_oelib_test');
        $result = $connection->select(['*'], 'tx_oelib_test', ['uid' => $firstRecordUid]);
        /** @var DatabaseRow|false $row */
        $row = $result->fetchAssociative();

        self::assertIsArray($row);
        self::assertSame(2, (int)$row['related_records']);
    }

    /**
     * @test
     */
    public function createRelationAndUpdateCounterCreatesRecordInRelationTable(): void
    {
        $firstRecordUid = $this->subject->createRecord('tx_oelib_test');
        $secondRecordUid = $this->subject->createRecord('tx_oelib_test');

        $this->subject->createRelationAndUpdateCounter(
            'tx_oelib_test',
            $firstRecordUid,
            $secondRecordUid,
            'related_records',
        );

        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test_article_mm');
        $count = $relationConnection->count('*', 'tx_oelib_test_article_mm', ['uid_local' => $firstRecordUid]);
        self::assertSame(1, $count);
    }

    /**
     * @test
     */
    public function createRelationAndUpdateCounterWithBidirectionalRelationIncreasesCounter(): void
    {
        $firstRecordUid = $this->subject->createRecord('tx_oelib_test');
        $secondRecordUid = $this->subject->createRecord('tx_oelib_test');

        $this->subject->createRelationAndUpdateCounter(
            'tx_oelib_test',
            $firstRecordUid,
            $secondRecordUid,
            'bidirectional',
        );

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_oelib_test');
        $result = $connection->select(['*'], 'tx_oelib_test', ['uid' => $firstRecordUid]);
        /** @var DatabaseRow|false $row */
        $row = $result->fetchAssociative();

        self::assertIsArray($row);
        self::assertSame(1, (int)$row['bidirectional']);
    }

    /**
     * @test
     */
    public function createRelationAndUpdateCounterWithBidirectionalIncreasesOppositeFieldCounterInForeignTable(): void
    {
        $firstRecordUid = $this->subject->createRecord('tx_oelib_test');
        $secondRecordUid = $this->subject->createRecord('tx_oelib_test');

        $this->subject->createRelationAndUpdateCounter(
            'tx_oelib_test',
            $firstRecordUid,
            $secondRecordUid,
            'bidirectional',
        );

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_oelib_test');
        $result = $connection->select(['*'], 'tx_oelib_test', ['uid' => $secondRecordUid]);
        /** @var DatabaseRow|false $row */
        $row = $result->fetchAssociative();

        self::assertIsArray($row);
        self::assertSame(1, (int)$row['related_records']);
    }

    /**
     * @test
     */
    public function createRelationAndUpdateCounterWithBidirectionalRelationCreatesRecordInRelationTable(): void
    {
        $firstRecordUid = $this->subject->createRecord('tx_oelib_test');
        $secondRecordUid = $this->subject->createRecord('tx_oelib_test');

        $this->subject->createRelationAndUpdateCounter(
            'tx_oelib_test',
            $firstRecordUid,
            $secondRecordUid,
            'bidirectional',
        );

        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test_article_mm');
        $count = $relationConnection->count(
            '*',
            'tx_oelib_test_article_mm',
            ['uid_local' => $secondRecordUid, 'uid_foreign' => $firstRecordUid],
        );
        self::assertSame(1, $count);
    }

    // Tests regarding cleanUpWithoutDatabase()

    /**
     * @test
     */
    public function cleanUpWithoutDatabaseRestoresCurrentScriptAfterCreateFakeFrontEnd(): void
    {
        $previous = Environment::getCurrentScript();
        $this->subject->createFakeFrontEnd($this->subject->createFrontEndPage());

        $this->subject->cleanUpWithoutDatabase();

        self::assertSame($previous, Environment::getCurrentScript());
    }

    /**
     * @test
     */
    public function cleanUpWithoutDatabaseUnsetsGlobalRequest(): void
    {
        $this->subject->createFakeFrontEnd($this->subject->createFrontEndPage());

        $this->subject->cleanUpWithoutDatabase();

        self::assertNull($GLOBALS['TYPO3_REQUEST'] ?? null);
    }

    // Tests regarding createFrontEndPage()

    /**
     * @test
     */
    public function createFrontEndPageCreatesFrontEndPageAndReturnsItsUid(): void
    {
        $uid = $this->subject->createFrontEndPage();

        self::assertNotSame(0, $uid);
        $connection = $this->getConnectionPool()->getConnectionForTable('pages');
        self::assertSame(1, $connection->count('*', 'pages', ['uid' => $uid]));
    }

    /**
     * @test
     */
    public function createFrontEndPageByDefaultPopulatesSlugWithPageUid(): void
    {
        $uid = $this->subject->createFrontEndPage();
        self::assertNotSame(0, $uid);

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages');
        $result = $connection->select(['*'], 'pages', ['uid' => $uid]);
        /** @var DatabaseRow|false $row */
        $row = $result->fetchAssociative();

        self::assertIsArray($row);
        self::assertSame('/' . $uid, $row['slug']);
    }

    /**
     * @test
     */
    public function createFrontEndPageSavesPageWithProvidedData(): void
    {
        $title = 'Test page';
        $uid = $this->subject->createFrontEndPage(0, ['title' => $title]);
        self::assertNotSame(0, $uid);

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages');
        $result = $connection->select(['*'], 'pages', ['uid' => $uid]);
        /** @var DatabaseRow|false $row */
        $row = $result->fetchAssociative();

        self::assertIsArray($row);
        self::assertSame($title, $row['title']);
    }

    /**
     * @test
     */
    public function createFrontEndPageCanUseSlugFromProvidedData(): void
    {
        $slug = '/home';
        $uid = $this->subject->createFrontEndPage(0, ['slug' => $slug]);
        self::assertNotSame(0, $uid);

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages');
        $result = $connection->select(['*'], 'pages', ['uid' => $uid]);
        /** @var DatabaseRow|false $row */
        $row = $result->fetchAssociative();

        self::assertIsArray($row);
        self::assertSame($slug, $row['slug']);
    }

    /**
     * @test
     */
    public function createFrontEndPageSetsPageDocumentType(): void
    {
        $uid = $this->subject->createFrontEndPage();
        self::assertNotSame(0, $uid);

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages');
        $result = $connection->select(['*'], 'pages', ['uid' => $uid]);
        /** @var DatabaseRow|false $row */
        $row = $result->fetchAssociative();

        self::assertIsArray($row);
        self::assertSame(1, (int)$row['doktype']);
    }

    /**
     * @test
     */
    public function createFrontEndPageByDefaultCreatesPageOnRootPage(): void
    {
        $uid = $this->subject->createFrontEndPage();
        self::assertNotSame(0, $uid);

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages');
        $result = $connection->select(['*'], 'pages', ['uid' => $uid]);
        /** @var DatabaseRow|false $row */
        $row = $result->fetchAssociative();

        self::assertIsArray($row);
        self::assertSame(0, (int)$row['pid']);
    }

    /**
     * @test
     */
    public function createFrontEndPageCanCreatePageOnOtherPage(): void
    {
        $parentUid = $this->subject->createFrontEndPage();
        $uid = $this->subject->createFrontEndPage($parentUid);

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages');
        $result = $connection->select(['*'], 'pages', ['uid' => $uid]);
        /** @var DatabaseRow|false $row */
        $row = $result->fetchAssociative();

        self::assertIsArray($row);
        self::assertSame($parentUid, (int)$row['pid']);
    }

    /**
     * @test
     */
    public function frontEndPageHasNoTitleByDefault(): void
    {
        $uid = $this->subject->createFrontEndPage();

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages');
        $result = $connection->select(['*'], 'pages', ['uid' => $uid]);
        /** @var DatabaseRow|false $row */
        $row = $result->fetchAssociative();

        self::assertIsArray($row);
        self::assertSame('', $row['title']);
    }

    // Tests regarding createSystemFolder()

    /**
     * @test
     */
    public function systemFolderCanBeCreated(): void
    {
        $uid = $this->subject->createSystemFolder();

        self::assertNotSame(0, $uid);

        $connection = $this->getConnectionPool()->getConnectionForTable('pages');
        self::assertSame(1, $connection->count('*', 'pages', ['uid' => $uid]));
    }

    /**
     * @test
     */
    public function createSystemFolderSetsCorrectDocumentType(): void
    {
        $uid = $this->subject->createSystemFolder();
        self::assertNotSame(0, $uid);

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages');
        $result = $connection->select(['*'], 'pages', ['uid' => $uid]);
        /** @var DatabaseRow|false $row */
        $row = $result->fetchAssociative();

        self::assertIsArray($row);
        self::assertSame(254, (int)$row['doktype']);
    }

    /**
     * @test
     */
    public function systemFolderWillBeCreatedOnRootPage(): void
    {
        $uid = $this->subject->createSystemFolder();

        self::assertNotSame(0, $uid);

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages');
        $result = $connection->select(['*'], 'pages', ['uid' => $uid]);
        /** @var DatabaseRow|false $row */
        $row = $result->fetchAssociative();

        self::assertIsArray($row);
        self::assertSame(0, (int)$row['pid']);
    }

    /**
     * @test
     */
    public function systemFolderCanBeCreatedOnOtherPage(): void
    {
        $parent = $this->subject->createSystemFolder();
        $uid = $this->subject->createSystemFolder($parent);
        self::assertNotSame(0, $uid);

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages');
        $result = $connection->select(['*'], 'pages', ['uid' => $uid]);
        /** @var DatabaseRow|false $row */
        $row = $result->fetchAssociative();

        self::assertIsArray($row);
        self::assertSame($parent, (int)$row['pid']);
    }

    /**
     * @test
     */
    public function systemFolderHasNoTitleByDefault(): void
    {
        $uid = $this->subject->createSystemFolder();

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages');
        $result = $connection->select(['*'], 'pages', ['uid' => $uid]);
        /** @var DatabaseRow|false $row */
        $row = $result->fetchAssociative();

        self::assertIsArray($row);
        self::assertSame('', $row['title']);
    }

    // Tests regarding createTemplate()

    /**
     * @test
     */
    public function templateCanBeCreatedOnNonRootPage(): void
    {
        $pageId = $this->subject->createFrontEndPage();
        $uid = $this->subject->createTemplate($pageId);

        self::assertNotSame(0, $uid);
        $connection = $this->getConnectionPool()->getConnectionForTable('sys_template');
        self::assertSame(1, $connection->count('*', 'sys_template', ['uid' => $uid]));
    }

    /**
     * @test
     */
    public function templateCannotBeCreatedOnRootPage(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$pageId must be > 0.');

        // @phpstan-ignore-next-line We're testing for a contract violation here.
        $this->subject->createTemplate(0);
    }

    /**
     * @test
     */
    public function templateCannotBeCreatedWithNegativePageNumber(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$pageId must be > 0.');

        // @phpstan-ignore-next-line We're testing for a contract violation here.
        $this->subject->createTemplate(-1);
    }

    /**
     * @test
     */
    public function templateInitiallyHasNoConfig(): void
    {
        $pageId = $this->subject->createFrontEndPage();
        $uid = $this->subject->createTemplate($pageId);

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('sys_template');
        $result = $connection->select(['*'], 'sys_template', ['uid' => $uid]);
        /** @var DatabaseRow|false $row */
        $row = $result->fetchAssociative();

        self::assertIsArray($row);
        self::assertNull($row['config']);
    }

    /**
     * @test
     */
    public function templateCanHaveConfig(): void
    {
        $pageId = $this->subject->createFrontEndPage();
        $uid = $this->subject->createTemplate(
            $pageId,
            ['config' => 'plugin.tx_oelib.test = 1'],
        );

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('sys_template');
        $result = $connection->select(['*'], 'sys_template', ['uid' => $uid]);
        /** @var DatabaseRow|false $row */
        $row = $result->fetchAssociative();

        self::assertIsArray($row);
        self::assertSame(
            'plugin.tx_oelib.test = 1',
            $row['config'],
        );
    }

    /**
     * @test
     */
    public function templateInitiallyHasNoConstants(): void
    {
        $pageId = $this->subject->createFrontEndPage();
        $uid = $this->subject->createTemplate($pageId);

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('sys_template');
        $result = $connection->select(['*'], 'sys_template', ['uid' => $uid]);
        /** @var DatabaseRow|false $row */
        $row = $result->fetchAssociative();

        self::assertIsArray($row);
        self::assertNull($row['constants']);
    }

    /**
     * @test
     */
    public function templateCanHaveConstants(): void
    {
        $pageId = $this->subject->createFrontEndPage();
        $uid = $this->subject->createTemplate(
            $pageId,
            ['constants' => 'plugin.tx_oelib.test = 1'],
        );

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('sys_template');
        $result = $connection->select(['*'], 'sys_template', ['uid' => $uid]);
        /** @var DatabaseRow|false $row */
        $row = $result->fetchAssociative();

        self::assertIsArray($row);
        self::assertSame('plugin.tx_oelib.test = 1', $row['constants']);
    }

    // Tests regarding createFrontEndUserGroup()

    /**
     * @test
     */
    public function frontEndUserGroupCanBeCreated(): void
    {
        $uid = $this->subject->createFrontEndUserGroup();

        self::assertNotSame(0, $uid);
        $connection = $this->getConnectionPool()->getConnectionForTable('fe_groups');
        self::assertSame(1, $connection->count('*', 'fe_groups', ['uid' => $uid]));
    }

    /**
     * @test
     */
    public function frontEndUserGroupHasNoTitleByDefault(): void
    {
        $uid = $this->subject->createFrontEndUserGroup();

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('fe_groups');
        $result = $connection->select(['*'], 'fe_groups', ['uid' => $uid]);
        /** @var DatabaseRow|false $row */
        $row = $result->fetchAssociative();

        self::assertIsArray($row);
        self::assertSame('', $row['title']);
    }

    /**
     * @test
     */
    public function frontEndUserGroupCanHaveTitle(): void
    {
        $uid = $this->subject->createFrontEndUserGroup(['title' => 'Test title']);

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('fe_groups');
        $result = $connection->select(['*'], 'fe_groups', ['uid' => $uid]);
        /** @var DatabaseRow|false $row */
        $row = $result->fetchAssociative();

        self::assertIsArray($row);
        self::assertSame('Test title', $row['title']);
    }

    // Tests regarding createFrontEndUser()

    /**
     * @test
     */
    public function frontEndUserCanBeCreated(): void
    {
        $uid = $this->subject->createFrontEndUser();
        self::assertNotSame(0, $uid);

        $connection = $this->getConnectionPool()->getConnectionForTable('fe_users');
        self::assertSame(1, $connection->count('*', 'fe_users', ['uid' => $uid]));
    }

    /**
     * @test
     */
    public function frontEndUserHasNoUserNameByDefault(): void
    {
        $uid = $this->subject->createFrontEndUser();

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('fe_users');
        $result = $connection->select(['*'], 'fe_users', ['uid' => $uid]);
        /** @var DatabaseRow|false $row */
        $row = $result->fetchAssociative();

        self::assertIsArray($row);
        self::assertSame('', $row['username']);
    }

    /**
     * @test
     */
    public function frontEndUserCanHaveUserName(): void
    {
        $uid = $this->subject->createFrontEndUser('', ['username' => 'Test name']);

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('fe_users');
        $result = $connection->select(['*'], 'fe_users', ['uid' => $uid]);
        /** @var DatabaseRow|false $row */
        $row = $result->fetchAssociative();

        self::assertIsArray($row);
        self::assertSame('Test name', $row['username']);
    }

    /**
     * @test
     */
    public function frontEndUserCanHaveSeveralUserGroups(): void
    {
        $feUserGroupUidOne = $this->subject->createFrontEndUserGroup();
        $feUserGroupUidTwo = $this->subject->createFrontEndUserGroup();
        $feUserGroupUidThree = $this->subject->createFrontEndUserGroup();
        $uid = $this->subject->createFrontEndUser(
            $feUserGroupUidOne . ', ' . $feUserGroupUidTwo . ', ' . $feUserGroupUidThree,
        );
        self::assertNotSame(0, $uid);

        $connection = $this->getConnectionPool()->getConnectionForTable('fe_users');
        self::assertSame(1, $connection->count('*', 'fe_users', ['uid' => $uid]));
    }

    /**
     * @test
     */
    public function frontEndUserMustHaveNoZeroUid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The column "uid" must not be set in $recordData.');

        $this->subject->createFrontEndUser('', ['uid' => 0]);
    }

    /**
     * @test
     */
    public function frontEndUserMustHaveNoNonZeroUid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The column "uid" must not be set in $recordData.');

        $this->subject->createFrontEndUser('', ['uid' => 99999]);
    }

    /**
     * @test
     */
    public function frontEndUserMustHaveNoZeroUserGroupInTheDataArray(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The column "usergroup" must not be set in $recordData.');

        $this->subject->createFrontEndUser('', ['usergroup' => 0]);
    }

    /**
     * @test
     */
    public function frontEndUserMustHaveNoNonZeroUserGroupInTheDataArray(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The column "usergroup" must not be set in $recordData.');

        $this->subject->createFrontEndUser('', ['usergroup' => 99999]);
    }

    /**
     * @test
     */
    public function frontEndUserMustHaveNoUserGroupListInTheDataArray(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The column "usergroup" must not be set in $recordData.');

        $this->subject->createFrontEndUser(
            '',
            ['usergroup' => '1,2,4,5'],
        );
    }

    /**
     * @test
     */
    public function createFrontEndUserWithEmptyGroupCreatesGroup(): void
    {
        $this->subject->createFrontEndUser('');

        $connection = $this->getConnectionPool()->getConnectionForTable('fe_groups');
        self::assertSame(1, $connection->count('*', 'fe_groups', []));
    }

    /**
     * @test
     */
    public function frontEndUserMustHaveNoZeroUserGroupEvenIfSeveralGroupsAreProvided(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            '$frontEndUserGroups must contain a comma-separated list of UIDs. Each UID must be > 0.',
        );

        $feUserGroupUidOne = $this->subject->createFrontEndUserGroup();
        $feUserGroupUidTwo = $this->subject->createFrontEndUserGroup();
        $feUserGroupUidThree = $this->subject->createFrontEndUserGroup();

        $this->subject->createFrontEndUser(
            $feUserGroupUidOne . ', ' . $feUserGroupUidTwo . ', 0, ' . $feUserGroupUidThree,
        );
    }

    /**
     * @test
     */
    public function frontEndUserMustHaveNoAlphabeticalCharactersInTheUserGroupList(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            '$frontEndUserGroups must contain a comma-separated list of UIDs. Each UID must be > 0.',
        );

        $feUserGroupUid = $this->subject->createFrontEndUserGroup();

        $this->subject->createFrontEndUser(
            $feUserGroupUid . ', abc',
        );
    }

    // Tests concerning fakeFrontend

    /**
     * @test
     */
    public function createFakeFrontEndCreatesGlobalFrontEnd(): void
    {
        $pageUid = $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd($pageUid);

        self::assertInstanceOf(TypoScriptFrontendController::class, $GLOBALS['TSFE']);
    }

    /**
     * @test
     */
    public function createFakeFrontEndCreatesFrontEndUser(): void
    {
        $pageUid = $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd($pageUid);

        self::assertInstanceOf(
            FrontendUserAuthentication::class,
            $this->getFrontEndController()->fe_user,
        );
    }

    /**
     * @test
     */
    public function createFakeFrontEndCreatesContentObjectRenderer(): void
    {
        $pageUid = $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd($pageUid);

        self::assertInstanceOf(ContentObjectRenderer::class, $this->getFrontEndController()->cObj);
    }

    /**
     * @test
     */
    public function createFakeFrontEndCreatesConfiguration(): void
    {
        $pageUid = $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd($pageUid);

        self::assertIsArray($this->getFrontEndController()->config);
    }

    /**
     * @test
     */
    public function loginUserIsFalseAfterCreateFakeFrontEnd(): void
    {
        $pageUid = $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd($pageUid);

        $isLoggedIn = (bool)$this->getContext()->getPropertyFromAspect('frontend.user', 'isLoggedIn');

        self::assertFalse($isLoggedIn);
    }

    /**
     * @test
     */
    public function createFakeFrontEndReturnsProvidedPageUid(): void
    {
        $pageUid = $this->subject->createFrontEndPage();

        self::assertSame(
            $pageUid,
            $this->subject->createFakeFrontEnd($pageUid),
        );
    }

    /**
     * @test
     */
    public function createFakeFrontEndUsesProvidedPageUidAsFrontEndId(): void
    {
        $pageUid = $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd($pageUid);

        self::assertSame($pageUid, (int)$this->getFrontEndController()->id);
    }

    /**
     * @test
     */
    public function getFakeFrontEndDomainReturnsDevDomain(): void
    {
        self::assertSame('typo3-test.dev', $this->subject->getFakeFrontEndDomain());
    }

    /**
     * @test
     */
    public function getFakeSiteUrlReturnsSiteUrlOfDevDomain(): void
    {
        self::assertSame('http://typo3-test.dev/', $this->subject->getFakeSiteUrl());
    }

    /**
     * @return array<string, array{0: string, 1: string|bool|null}>
     */
    public function globalsDataProvider(): array
    {
        return [
            'HTTP_HOST' => ['HTTP_HOST', 'typo3-test.dev'],
            'TYPO3_HOST_ONLY' => ['TYPO3_HOST_ONLY', 'typo3-test.dev'],
            'TYPO3_PORT' => ['TYPO3_PORT', ''],
            'QUERY_STRING' => ['QUERY_STRING', ''],
            'HTTP_REFERER' => ['HTTP_REFERER', 'http://typo3-test.dev/'],
            'TYPO3_REQUEST_HOST' => ['TYPO3_REQUEST_HOST', 'http://typo3-test.dev'],
            'TYPO3_REQUEST_SCRIPT' => ['TYPO3_REQUEST_SCRIPT', 'http://typo3-test.dev/index.php'],
            'TYPO3_REQUEST_DIR' => ['TYPO3_REQUEST_DIR', 'http://typo3-test.dev/'],
            'TYPO3_SITE_URL' => ['TYPO3_SITE_URL', 'http://typo3-test.dev/'],
            'TYPO3_SSL' => ['TYPO3_SSL', false],
            'TYPO3_REV_PROXY' => ['TYPO3_REV_PROXY', false],
            'SCRIPT_NAME' => ['SCRIPT_NAME', '/index.php'],
            'TYPO3_DOCUMENT_ROOT' => ['TYPO3_DOCUMENT_ROOT', '/var/www/html/public'],
            'SCRIPT_FILENAME' => ['SCRIPT_FILENAME', '/var/www/html/public/index.php'],
            'REMOTE_ADDR' => ['REMOTE_ADDR', '127.0.0.1'],
            'REMOTE_HOST' => ['REMOTE_HOST', ''],
            'HTTP_USER_AGENT' => [
                'HTTP_USER_AGENT',
                'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:121.0) Gecko/20100101 Firefox/121.0',
            ],
            'HTTP_ACCEPT_LANGUAGE' => ['HTTP_ACCEPT_LANGUAGE', 'de,en-US;q=0.7,en;q=0.3'],
            'HTTP_ACCEPT_ENCODING' => ['HTTP_ACCEPT_ENCODING', 'gzip, deflate, br'],
        ];
    }

    /**
     * @test
     *
     * @param string|bool|null $expected
     *
     * @dataProvider globalsDataProvider
     */
    public function createFakeFrontEndPopulatesGlobals(string $key, $expected): void
    {
        $this->subject->createFakeFrontEnd($this->subject->createFrontEndPage());

        self::assertSame($expected, GeneralUtility::getIndpEnv($key));
    }

    /**
     * @return array<non-empty-string, array{0: non-empty-string, 1: non-empty-string}>
     */
    public function pageSpecificGlobalsWithPageUidDataProvider(): array
    {
        return [
            'REQUEST_URI' => ['REQUEST_URI', '/%1s'],
            'TYPO3_REQUEST_URL' => ['TYPO3_REQUEST_URL', 'http://typo3-test.dev/%1s'],
            'TYPO3_SITE_SCRIPT' => ['TYPO3_SITE_SCRIPT', '%1s'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider pageSpecificGlobalsWithPageUidDataProvider
     */
    public function createFakeFrontWithWithPageUsesGivenPageInUri(string $key, string $expectedWithPlaceholder): void
    {
        $pageUid = $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd($pageUid);

        $expected = \sprintf($expectedWithPlaceholder, $pageUid);
        self::assertSame($expected, GeneralUtility::getIndpEnv($key));
    }

    /**
     * @test
     */
    public function fakeFrontEndAllowsCreatingTypoLinkToRootPage(): void
    {
        $rootPageUid = $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd($rootPageUid);

        $typolinkUrl = $this->getFrontEndController()->cObj->typoLink_URL(['parameter' => $rootPageUid]);

        self::assertSame('/' . $rootPageUid, $typolinkUrl);
    }

    /**
     * @test
     */
    public function fakeFrontEndAllowsCreatingTypoLinkToSubpageOfRootPage(): void
    {
        $rootPageUid = $this->subject->createFrontEndPage();
        $subpageUid = $this->subject->createFrontEndPage($rootPageUid);
        $this->subject->createFakeFrontEnd($rootPageUid);

        $typolinkUrl = $this->getFrontEndController()->cObj->typoLink_URL(['parameter' => $subpageUid]);

        self::assertSame('/' . $subpageUid, $typolinkUrl);
    }

    /**
     * @test
     */
    public function fakeFrontEndAllowsLocationHeaderUrlWithLinkCreatedViaTypolink(): void
    {
        $rootPageUid = $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd($rootPageUid);

        $typolinkUrl = $this->getFrontEndController()->cObj->typoLink_URL(['parameter' => $rootPageUid]);

        $expectedUrl = $this->subject->getFakeSiteUrl() . $rootPageUid;
        self::assertSame($expectedUrl, GeneralUtility::locationHeaderUrl($typolinkUrl));
    }

    /**
     * @test
     */
    public function createFakeFrontEndOverwritesCurrentScript(): void
    {
        $previous = Environment::getCurrentScript();
        $this->subject->createFakeFrontEnd($this->subject->createFrontEndPage());

        self::assertNotSame($previous, Environment::getCurrentScript());
    }

    /**
     * @test
     */
    public function createFakeFrontEndSetsRequest(): void
    {
        $pageUid = $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd($pageUid);

        self::assertInstanceOf(ServerRequest::class, $GLOBALS['TYPO3_REQUEST']);
    }

    /**
     * @test
     */
    public function createFakeFrontEndReplacesExistingGlobalRequest(): void
    {
        $previousRequest = $this->createStub(ServerRequestInterface::class);
        $GLOBALS['TYPO3_REQUEST'] = $previousRequest;

        $this->subject->createFakeFrontEnd($this->subject->createFrontEndPage());

        self::assertNotSame($previousRequest, $GLOBALS['TYPO3_REQUEST'] ?? null);
    }

    /**
     * @test
     */
    public function createFakeFrontEndSetsFrontEndApplicationTypeInRequest(): void
    {
        $pageUid = $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd($pageUid);

        $request = $GLOBALS['TYPO3_REQUEST'];
        self::assertInstanceOf(ServerRequest::class, $request);
        self::assertSame(SystemEnvironmentBuilder::REQUESTTYPE_FE, $request->getAttribute('applicationType'));
    }

    /**
     * @test
     */
    public function createFakeFrontEndSetsLanguageInRequest(): void
    {
        $pageUid = $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd($pageUid);

        $request = $GLOBALS['TYPO3_REQUEST'];
        self::assertInstanceOf(ServerRequest::class, $request);

        self::assertInstanceOf(SiteLanguage::class, $request->getAttribute('language'));
    }

    /**
     * @test
     */
    public function createFakeFrontEndSetsLanguageInRequestToDefault(): void
    {
        $pageUid = $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd($pageUid);

        $request = $GLOBALS['TYPO3_REQUEST'];
        self::assertInstanceOf(ServerRequest::class, $request);
        $language = $request->getAttribute('language');
        self::assertInstanceOf(SiteLanguage::class, $language);

        $uri = new Uri($this->subject->getFakeSiteUrl());
        $expectedLanguage = new SiteLanguage(0, 'en_US.UTF-8', $uri, []);
        self::assertEquals($expectedLanguage, $language);
    }

    // Tests regarding user login and logout

    /**
     * @test
     */
    public function logoutFrontEndUserSetsLoginUserToFalse(): void
    {
        $pageUid = $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd($pageUid);

        $this->subject->logoutFrontEndUser();

        $isLoggedIn = (bool)$this->getContext()->getPropertyFromAspect('frontend.user', 'isLoggedIn');

        self::assertFalse($isLoggedIn);
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function logoutFrontEndUserCanBeCalledTwoTimes(): void
    {
        $pageUid = $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd($pageUid);

        $this->subject->logoutFrontEndUser();
        $this->subject->logoutFrontEndUser();
    }

    /**
     * @test
     */
    public function createAndLoginFrontEndUserCreatesFrontEndUser(): void
    {
        $pageUid = $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd($pageUid);
        $this->subject->createAndLoginFrontEndUser();

        $connection = $this->getConnectionPool()->getConnectionForTable('fe_users');
        self::assertSame(1, $connection->count('*', 'fe_users', []));
    }

    /**
     * @test
     */
    public function createAndLoginFrontEndUserWithRecordDataCreatesFrontEndUserWithThatData(): void
    {
        $name = 'John Doe';
        $pageUid = $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd($pageUid);

        $this->subject->createAndLoginFrontEndUser('', ['name' => $name]);

        $connection = $this->getConnectionPool()->getConnectionForTable('fe_users');
        self::assertSame(1, $connection->count('*', 'fe_users', ['name' => $name]));
    }

    /**
     * @test
     */
    public function createAndLoginFrontEndUserLogsInFrontEndUser(): void
    {
        $pageUid = $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd($pageUid);
        $this->subject->createAndLoginFrontEndUser();

        $isLoggedIn = (bool)$this->getContext()->getPropertyFromAspect('frontend.user', 'isLoggedIn');
        self::assertTrue($isLoggedIn);
    }

    /**
     * @test
     */
    public function createAndLoginFrontEndUserWithFrontEndUserGroupCreatesFrontEndUser(): void
    {
        $pageUid = $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd($pageUid);
        $frontEndUserGroupUid = $this->subject->createFrontEndUserGroup();
        $this->subject->createAndLoginFrontEndUser($frontEndUserGroupUid);

        $connection = $this->getConnectionPool()->getConnectionForTable('fe_users');
        self::assertSame(1, $connection->count('*', 'fe_users', []));
    }

    /**
     * @test
     */
    public function createAndLoginFrontEndUserWithFrontEndUserGroupCreatesFrontEndUserWithGivenGroup(): void
    {
        $pageUid = $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd($pageUid);
        $frontEndUserGroupUid = $this->subject->createFrontEndUserGroup();
        $frontEndUserUid = $this->subject->createAndLoginFrontEndUser(
            $frontEndUserGroupUid,
        );

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('fe_users');
        $result = $connection->select(['*'], 'fe_users', ['uid' => $frontEndUserUid]);
        /** @var DatabaseRow|false $row */
        $row = $result->fetchAssociative();

        self::assertIsArray($row);
        self::assertSame($frontEndUserGroupUid, (int)$row['usergroup']);
    }

    /**
     * @test
     */
    public function createAndLoginFrontEndUserWithFrontEndUserGroupDoesNotCreateFrontEndUserGroup(): void
    {
        $pageUid = $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd($pageUid);
        $frontEndUserGroupUid = $this->subject->createFrontEndUserGroup();

        $this->subject->createAndLoginFrontEndUser($frontEndUserGroupUid);

        $connection = $this->getConnectionPool()->getConnectionForTable('fe_groups');
        self::assertSame(1, $connection->count('*', 'fe_groups', []));
    }

    /**
     * @test
     */
    public function createAndLoginFrontEndUserWithFrontEndUserGroupLogsInFrontEndUser(): void
    {
        $pageUid = $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd($pageUid);
        $frontEndUserGroupUid = $this->subject->createFrontEndUserGroup();
        $this->subject->createAndLoginFrontEndUser($frontEndUserGroupUid);

        $isLoggedIn = (bool)$this->getContext()->getPropertyFromAspect('frontend.user', 'isLoggedIn');
        self::assertTrue($isLoggedIn);
    }
}
