<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Templating;

use OliverKlee\Oelib\Templating\Template;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Oelib\Templating\Template
 */
final class TemplateTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = ['oliverklee/oelib'];

    protected bool $initializeDatabase = false;

    private Template $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new Template();
    }

    #[Test]
    public function processTemplateFromFileProcessesTemplateFromFile(): void
    {
        $this->subject->processTemplateFromFile('EXT:oelib/Tests/Functional/Templating/Fixtures/Template.html');

        self::assertSame("Hello world!\n", $this->subject->render());
    }
}
