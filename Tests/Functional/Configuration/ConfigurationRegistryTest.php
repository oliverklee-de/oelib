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

    protected function setUp(): void
    {
        parent::setUp();
        $this->testingFramework = new TestingFramework('tx_oelib');
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUpWithoutDatabase();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function isAvailableViaContainer(): void
    {
        $instance = $this->get(ConfigurationRegistry::class);

        self::assertInstanceOf(ConfigurationRegistry::class, $instance);
    }

    /**
     * @test
     */
    public function getForNonEmptyNamespaceReturnsConfigurationInstance(): void
    {
        PageFinder::getInstance()->setPageUid(
            $this->testingFramework->createFrontEndPage(),
        );

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
        PageFinder::getInstance()->setPageUid(
            $this->testingFramework->createFrontEndPage(),
        );

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
        PageFinder::getInstance()->forceSource(PageFinder::SOURCE_FRONT_END);

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
}
