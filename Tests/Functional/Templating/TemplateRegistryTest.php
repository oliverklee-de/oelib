<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Templating;

use OliverKlee\Oelib\Templating\Template;
use OliverKlee\Oelib\Templating\TemplateRegistry;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Oelib\Templating\TemplateRegistry
 */
final class TemplateRegistryTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = ['oliverklee/oelib'];

    protected bool $initializeDatabase = false;

    private TemplateRegistry $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = $this->get(TemplateRegistry::class);
    }

    protected function tearDown(): void
    {
        TemplateRegistry::purgeInstance();

        parent::tearDown();
    }

    /**
     * @test
     */
    public function isAvailableViaContainer(): void
    {
        self::assertInstanceOf(TemplateRegistry::class, $this->subject);
    }

    /**
     * @test
     */
    public function getForExistingTemplateFileNameReturnsTemplate(): void
    {
        self::assertInstanceOf(
            Template::class,
            TemplateRegistry::get('EXT:oelib/Tests/Functional/Templating/Fixtures/Template.html'),
        );
    }

    /**
     * @test
     */
    public function getForExistingTemplateFileNameCalledTwoTimesReturnsNewInstance(): void
    {
        self::assertNotSame(
            TemplateRegistry::get('EXT:oelib/Tests/Functional/Templating/Fixtures/Template.html'),
            TemplateRegistry::get('EXT:oelib/Tests/Functional/Templating/Fixtures/Template.html'),
        );
    }

    /**
     * @test
     */
    public function getForExistingTemplateFileNameReturnsProcessedTemplate(): void
    {
        $template = TemplateRegistry::get('EXT:oelib/Tests/Functional/Templating/Fixtures/Template.html');

        self::assertSame(
            "Hello world!\n",
            $template->getSubpart(),
        );
    }

    /**
     * @test
     */
    public function getByFileNameForExistingTemplateFileNameReturnsTemplate(): void
    {
        self::assertInstanceOf(
            Template::class,
            $this->subject->getByFileName('EXT:oelib/Tests/Functional/Templating/Fixtures/Template.html'),
        );
    }

    /**
     * @test
     */
    public function getByFileNameForExistingTemplateFileNameCalledTwoTimesReturnsNewInstance(): void
    {
        self::assertNotSame(
            $this->subject->getByFileName('EXT:oelib/Tests/Functional/Templating/Fixtures/Template.html'),
            $this->subject->getByFileName('EXT:oelib/Tests/Functional/Templating/Fixtures/Template.html'),
        );
    }

    /**
     * @test
     */
    public function getByFileNameForExistingTemplateFileNameReturnsProcessedTemplate(): void
    {
        $template = $this->subject->getByFileName('EXT:oelib/Tests/Functional/Templating/Fixtures/Template.html');

        self::assertSame(
            "Hello world!\n",
            $template->getSubpart(),
        );
    }
}
