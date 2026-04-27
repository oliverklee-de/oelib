<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Configuration;

use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\Configuration\PageFinder;
use OliverKlee\Oelib\Configuration\TypoScriptConfiguration;
use OliverKlee\Oelib\Testing\TestingFramework;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Oelib\Configuration\ConfigurationRegistry
 */
final class ConfigurationRegistryTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = ['oliverklee/oelib'];

    private TestingFramework $testingFramework;

    private ConfigurationRegistry $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testingFramework = new TestingFramework('tx_oelib');

        $this->subject = $this->get(ConfigurationRegistry::class);
    }

    protected function tearDown(): void
    {
        ConfigurationRegistry::purgeInstance();
        PageFinder::purgeInstance();
        $this->testingFramework->cleanUpWithoutDatabase();

        parent::tearDown();
    }

    /**
     * @test
     */
    public function isAvailableViaContainer(): void
    {
        self::assertInstanceOf(ConfigurationRegistry::class, $this->subject);
    }

    /**
     * @test
     */
    public function getForNonEmptyNamespaceReturnsConfigurationInstance(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/ConfigurationRegistry/PageWithTemplate.csv');
        PageFinder::getInstance()->setPageUid(1);

        self::assertInstanceOf(
            TypoScriptConfiguration::class,
            ConfigurationRegistry::get('plugin.tx_oelib'),
        );
    }

    /**
     * @test
     */
    public function getForTheSameNamespaceCalledTwoTimesReturnsTheSameInstance(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/ConfigurationRegistry/PageWithTemplate.csv');
        PageFinder::getInstance()->setPageUid(1);

        self::assertSame(
            ConfigurationRegistry::get('plugin.tx_oelib'),
            ConfigurationRegistry::get('plugin.tx_oelib'),
        );
    }

    /**
     * @test
     */
    public function getReturnsDataFromTypoScriptSetupFromManuallySetPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/ConfigurationRegistry/PageWithTemplate.csv');

        PageFinder::getInstance()->setPageUid(1);

        self::assertSame(
            42,
            ConfigurationRegistry::get('plugin.tx_oelib')->getAsInteger('test'),
        );
    }

    /**
     * @test
     */
    public function getReturnsDataFromTypoScriptSetupFromBackEndPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/ConfigurationRegistry/PageWithTemplate.csv');
        $_GET['id'] = 1;

        PageFinder::getInstance()->forceSource(PageFinder::SOURCE_BACK_END);

        self::assertSame(
            42,
            ConfigurationRegistry::get('plugin.tx_oelib')->getAsInteger('test'),
        );
    }

    /**
     * @test
     */
    public function getReturnsDataFromTypoScriptSetupFromFrontEndPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/ConfigurationRegistry/PageWithTemplate.csv');

        $this->testingFramework->createFakeFrontEnd(1);
        $pageFinder = PageFinder::getInstance();
        $pageFinder->setPageUid(1);
        $pageFinder->forceSource(PageFinder::SOURCE_FRONT_END);

        self::assertSame(
            42,
            ConfigurationRegistry::get('plugin.tx_oelib')->getAsInteger('test'),
        );
    }

    /**
     * @test
     */
    public function getAfterSetReturnsManuallySetConfigurationEvenIfThereIsAPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/ConfigurationRegistry/PageWithTemplate.csv');
        PageFinder::getInstance()->setPageUid(1);

        $configuration = new DummyConfiguration();
        ConfigurationRegistry::getInstance()->set('plugin.tx_oelib', $configuration);

        self::assertSame($configuration, ConfigurationRegistry::get('plugin.tx_oelib'));
    }

    /**
     * @test
     */
    public function getByNamespaceForNonEmptyNamespaceReturnsConfigurationInstance(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/ConfigurationRegistry/PageWithTemplate.csv');
        PageFinder::getInstance()->setPageUid(1);

        self::assertInstanceOf(
            TypoScriptConfiguration::class,
            $this->subject->getByNamespace('plugin.tx_oelib'),
        );
    }

    /**
     * @test
     */
    public function getByNamespaceForTheSameNamespaceCalledTwoTimesReturnsTheSameInstance(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/ConfigurationRegistry/PageWithTemplate.csv');
        PageFinder::getInstance()->setPageUid(1);

        self::assertSame(
            $this->subject->getByNamespace('plugin.tx_oelib'),
            $this->subject->getByNamespace('plugin.tx_oelib'),
        );
    }

    /**
     * @test
     */
    public function getByNamespaceReturnsDataFromTypoScriptSetupFromManuallySetPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/ConfigurationRegistry/PageWithTemplate.csv');
        PageFinder::getInstance()->setPageUid(1);

        self::assertSame(
            42,
            $this->subject->getByNamespace('plugin.tx_oelib')->getAsInteger('test'),
        );
    }

    /**
     * @test
     */
    public function getByNamespaceReturnsDataFromTypoScriptSetupFromBackEndPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/ConfigurationRegistry/PageWithTemplate.csv');
        $_GET['id'] = 1;

        PageFinder::getInstance()->forceSource(PageFinder::SOURCE_BACK_END);

        self::assertSame(
            42,
            $this->subject->getByNamespace('plugin.tx_oelib')->getAsInteger('test'),
        );
    }

    /**
     * @test
     */
    public function getByNamespaceReturnsDataFromTypoScriptSetupFromFrontEndPage(): void
    {
        self::markTestIncomplete(
            'This test currently fails if run after getReturnsDataFromTypoScriptSetupFromFrontEndPage '
            . 'as the TypoScript parsing runs into an endless loop. There is some cross-pollution between these tests, '
            . 'and we need to find out how to properly clean up after them to avoid that.',
        );

        $this->importCSVDataSet(__DIR__ . '/Fixtures/ConfigurationRegistry/PageWithTemplate.csv');

        $this->testingFramework->createFakeFrontEnd(1);
        $pageFinder = PageFinder::getInstance();
        $pageFinder->setPageUid(1);
        $pageFinder->forceSource(PageFinder::SOURCE_FRONT_END);

        self::assertSame(
            42,
            $this->subject->getByNamespace('plugin.tx_oelib')->getAsInteger('test'),
        );
    }

    /**
     * @test
     */
    public function getByNamespaceAfterSetReturnsManuallySetConfigurationEvenIfThereIsAPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/ConfigurationRegistry/PageWithTemplate.csv');
        PageFinder::getInstance()->setPageUid(1);

        $configuration = new DummyConfiguration();
        $this->subject->set('plugin.tx_oelib', $configuration);

        self::assertSame($configuration, $this->subject->getByNamespace('plugin.tx_oelib'));
    }
}
