<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Templating;

use OliverKlee\Oelib\Templating\Template;
use OliverKlee\Oelib\Templating\TemplateRegistry;
use PHPUnit\Framework\Attributes\Test;
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

    #[Test]
    public function isAvailableViaContainer(): void
    {
        self::assertInstanceOf(TemplateRegistry::class, $this->subject);
    }

    #[Test]
    public function getByFileNameForExistingTemplateFileNameReturnsTemplate(): void
    {
        self::assertInstanceOf(
            Template::class,
            $this->subject->getByFileName('EXT:oelib/Tests/Functional/Templating/Fixtures/Template.html'),
        );
    }

    #[Test]
    public function getByFileNameForExistingTemplateFileNameCalledTwoTimesReturnsNewInstance(): void
    {
        self::assertNotSame(
            $this->subject->getByFileName('EXT:oelib/Tests/Functional/Templating/Fixtures/Template.html'),
            $this->subject->getByFileName('EXT:oelib/Tests/Functional/Templating/Fixtures/Template.html'),
        );
    }

    #[Test]
    public function getByFileNameForExistingTemplateFileNameReturnsProcessedTemplate(): void
    {
        $template = $this->subject->getByFileName('EXT:oelib/Tests/Functional/Templating/Fixtures/Template.html');

        self::assertSame(
            "Hello world!\n",
            $template->getSubpart(),
        );
    }
}
