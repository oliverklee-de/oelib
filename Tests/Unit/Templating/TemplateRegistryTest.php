<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Templating;

use OliverKlee\Oelib\Templating\Template;
use OliverKlee\Oelib\Templating\TemplateRegistry;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Oelib\Templating\TemplateRegistry
 */
final class TemplateRegistryTest extends UnitTestCase
{
    private TemplateRegistry $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new TemplateRegistry();
    }

    protected function tearDown(): void
    {
        TemplateRegistry::purgeInstance();

        parent::tearDown();
    }

    /**
     * @test
     */
    public function getInstanceReturnsTemplateRegistryInstance(): void
    {
        self::assertInstanceOf(
            TemplateRegistry::class,
            TemplateRegistry::getInstance(),
        );
    }

    /**
     * @test
     */
    public function getInstanceTwoTimesReturnsSameInstance(): void
    {
        self::assertSame(
            TemplateRegistry::getInstance(),
            TemplateRegistry::getInstance(),
        );
    }

    /**
     * @test
     */
    public function getInstanceAfterPurgeInstanceReturnsNewInstance(): void
    {
        $firstInstance = TemplateRegistry::getInstance();
        TemplateRegistry::purgeInstance();

        self::assertNotSame(
            $firstInstance,
            TemplateRegistry::getInstance(),
        );
    }

    /**
     * @test
     */
    public function getForEmptyTemplateFileNameReturnsTemplateInstance(): void
    {
        self::assertInstanceOf(
            Template::class,
            TemplateRegistry::get(''),
        );
    }

    /**
     * @test
     */
    public function getForEmptyTemplateFileNameCalledTwoTimesReturnsNewInstance(): void
    {
        self::assertNotSame(
            TemplateRegistry::get(''),
            TemplateRegistry::get(''),
        );
    }

    /**
     * @test
     */
    public function getByFileNameForEmptyTemplateFileNameReturnsTemplateInstance(): void
    {
        self::assertInstanceOf(
            Template::class,
            $this->subject->getByFileName(''),
        );
    }

    /**
     * @test
     */
    public function getByFileNameForEmptyTemplateFileNameCalledTwoTimesReturnsNewInstance(): void
    {
        self::assertNotSame(
            $this->subject->getByFileName(''),
            $this->subject->getByFileName(''),
        );
    }
}
