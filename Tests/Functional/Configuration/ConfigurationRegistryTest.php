<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Configuration;

use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\Configuration\PageFinder;
use OliverKlee\Oelib\Configuration\TypoScriptConfiguration;
use OliverKlee\Oelib\Testing\TestingFramework;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Information\Typo3Version;
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

        $this->testingFramework = $this->get(TestingFramework::class);

        $this->subject = $this->get(ConfigurationRegistry::class);
    }

    protected function tearDown(): void
    {
        PageFinder::purgeInstance();
        $this->testingFramework->cleanUpWithoutDatabase();

        parent::tearDown();
    }

    #[Test]
    public function isAvailableViaContainer(): void
    {
        self::assertInstanceOf(ConfigurationRegistry::class, $this->subject);
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function setTwoTimesForTheSameNamespaceDoesNotFail(): void
    {
        $this->subject->set('foo', new DummyConfiguration());
        $this->subject->set('foo', new DummyConfiguration());
    }

    #[Test]
    public function getByNamespaceForEmptyNamespaceThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$namespace must not be empty.');
        $this->expectExceptionCode(1_331_318_549);

        // @phpstan-ignore-next-line We are explicitly checking for a contract violation here.
        $this->subject->getByNamespace('');
    }

    #[Test]
    public function getByNamespaceAfterSetWithTypoScriptConfigurationReturnsTheSetInstance(): void
    {
        $configuration = new TypoScriptConfiguration();

        $this->subject->set('foo', $configuration);

        self::assertSame($configuration, $this->subject->getByNamespace('foo'));
    }

    #[Test]
    public function getByNamespaceAfterSetWithDummyConfigurationReturnsTheSetInstance(): void
    {
        $configuration = new DummyConfiguration();

        $this->subject->set('foo', $configuration);

        self::assertSame($configuration, $this->subject->getByNamespace('foo'));
    }

    #[Test]
    public function getByNamespaceForNonEmptyNamespaceReturnsConfigurationInstance(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/ConfigurationRegistry/PageWithTemplate.csv');
        PageFinder::getInstance()->setPageUid(1);

        self::assertInstanceOf(
            TypoScriptConfiguration::class,
            $this->subject->getByNamespace('plugin.tx_oelib'),
        );
    }

    #[Test]
    public function getByNamespaceForTheSameNamespaceCalledTwoTimesReturnsTheSameInstance(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/ConfigurationRegistry/PageWithTemplate.csv');
        PageFinder::getInstance()->setPageUid(1);

        self::assertSame(
            $this->subject->getByNamespace('plugin.tx_oelib'),
            $this->subject->getByNamespace('plugin.tx_oelib'),
        );
    }

    #[Test]
    public function getByNamespaceReturnsDataFromTypoScriptSetupFromManuallySetPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/ConfigurationRegistry/PageWithTemplate.csv');
        PageFinder::getInstance()->setPageUid(1);

        self::assertSame(
            42,
            $this->subject->getByNamespace('plugin.tx_oelib')->getAsInteger('test'),
        );
    }

    #[Test]
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

    #[Test]
    public function getByNamespaceReturnsDataFromTypoScriptSetupFromFrontEndPage(): void
    {
        if ((new Typo3Version())->getMajorVersion() >= 13) {
            self::markTestSkipped('This feature is only available in the testing framework for TYPO3 <= 12LTS.');
        }

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
     * This is the same as the previous test, but we are testing that the previous test does not leave any locks
     * in our way.
     */
    #[Test]
    public function getByNamespaceReturnsDataFromTypoScriptSetupFromFrontEndPageAgain(): void
    {
        if ((new Typo3Version())->getMajorVersion() >= 13) {
            self::markTestSkipped('This feature is only available in the testing framework for TYPO3 <= 12LTS.');
        }

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

    #[Test]
    public function getByNamespaceAfterSetReturnsManuallySetConfigurationEvenIfThereIsAPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/ConfigurationRegistry/PageWithTemplate.csv');
        PageFinder::getInstance()->setPageUid(1);

        $configuration = new DummyConfiguration();
        $this->subject->set('plugin.tx_oelib', $configuration);

        self::assertSame($configuration, $this->subject->getByNamespace('plugin.tx_oelib'));
    }
}
