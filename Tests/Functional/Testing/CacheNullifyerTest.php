<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Testing;

use OliverKlee\Oelib\Testing\CacheNullifyer;
use TYPO3\CMS\Core\Cache\Backend\BackendInterface;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Oelib\Testing\CacheNullifyer
 */
final class CacheNullifyerTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = ['oliverklee/oelib'];

    protected bool $initializeDatabase = false;

    private CacheNullifyer $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = $this->get(CacheNullifyer::class);
    }

    /**
     * @test
     */
    public function isSingleton(): void
    {
        self::assertInstanceOf(SingletonInterface::class, $this->subject);
    }

    /**
     * @return array<string, array{0: non-empty-string}>
     */
    public function coreCachesVersion11DataProvider(): array
    {
        return [
            'assets' => ['assets'],
            'core' => ['extbase'],
            'extbase' => ['extbase'],
            'fluid_template' => ['fluid_template'],
            'hash' => ['hash'],
            'imagesizes' => ['imagesizes'],
            'l10n' => ['l10n'],
            'pages' => ['pages'],
            'pagesection' => ['pagesection'],
            'rootline' => ['rootline'],
            'ratelimiter' => ['runtime'],
            'runtime' => ['runtime'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider coreCachesVersion11DataProvider
     */
    public function setAllCoreCachesSetsAllCachesForVersion11(string $identifier): void
    {
        if ((new Typo3Version())->getMajorVersion() !== 11) {
            self::markTestSkipped('This test is only for TYPO3 v11.');
        }

        $this->subject->setAllCoreCaches();

        self::assertTrue(GeneralUtility::makeInstance(CacheManager::class)->hasCache($identifier));
    }

    /**
     * @test
     *
     * @dataProvider coreCachesVersion11DataProvider
     */
    public function setAllCoreCachesSetsACacheFrontendForAllCachesForVersion11(string $identifier): void
    {
        if ((new Typo3Version())->getMajorVersion() !== 11) {
            self::markTestSkipped('This test is only for TYPO3 v11.');
        }

        $this->subject->setAllCoreCaches();

        $cache = GeneralUtility::makeInstance(CacheManager::class)->getCache($identifier);
        self::assertInstanceOf(FrontendInterface::class, $cache);
    }

    /**
     * @test
     *
     * @dataProvider coreCachesVersion11DataProvider
     */
    public function setAllCoreCachesSetsACacheBackendForAllCachesForVersion11(string $identifier): void
    {
        if ((new Typo3Version())->getMajorVersion() !== 11) {
            self::markTestSkipped('This test is only for TYPO3 v11.');
        }

        $this->subject->setAllCoreCaches();

        $cache = GeneralUtility::makeInstance(CacheManager::class)->getCache($identifier);
        self::assertInstanceOf(FrontendInterface::class, $cache);
        self::assertInstanceOf(BackendInterface::class, $cache->getBackend());
    }

    /**
     * @return array<string, array{0: non-empty-string}>
     */
    public function coreCachesVersion12DataProvider(): array
    {
        return [
            'assets' => ['assets'],
            'core' => ['extbase'],
            'database_schema' => ['extbase'],
            'extbase' => ['extbase'],
            'fluid_template' => ['fluid_template'],
            'hash' => ['hash'],
            'imagesizes' => ['imagesizes'],
            'l10n' => ['l10n'],
            'pages' => ['pages'],
            'rootline' => ['rootline'],
            'ratelimiter' => ['runtime'],
            'runtime' => ['runtime'],
            'typoscript' => ['runtime'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider coreCachesVersion12DataProvider
     */
    public function setAllCoreCachesSetsAllCachesForVersion12(string $identifier): void
    {
        if ((new Typo3Version())->getMajorVersion() !== 12) {
            self::markTestSkipped('This test is only for TYPO3 v12.');
        }

        $this->subject->setAllCoreCaches();

        self::assertTrue(GeneralUtility::makeInstance(CacheManager::class)->hasCache($identifier));
    }

    /**
     * @test
     *
     * @dataProvider coreCachesVersion12DataProvider
     */
    public function setAllCoreCachesSetsACacheFrontendForAllCachesForVersion12(string $identifier): void
    {
        if ((new Typo3Version())->getMajorVersion() !== 12) {
            self::markTestSkipped('This test is only for TYPO3 v12.');
        }

        $this->subject->setAllCoreCaches();

        $cache = GeneralUtility::makeInstance(CacheManager::class)->getCache($identifier);
        self::assertInstanceOf(FrontendInterface::class, $cache);
    }

    /**
     * @test
     *
     * @dataProvider coreCachesVersion12DataProvider
     */
    public function setAllCoreCachesSetsACacheBackendForAllCachesForVersion12(string $identifier): void
    {
        if ((new Typo3Version())->getMajorVersion() !== 12) {
            self::markTestSkipped('This test is only for TYPO3 v12.');
        }

        $this->subject->setAllCoreCaches();

        $cache = GeneralUtility::makeInstance(CacheManager::class)->getCache($identifier);
        self::assertInstanceOf(FrontendInterface::class, $cache);
        self::assertInstanceOf(BackendInterface::class, $cache->getBackend());
    }
}
