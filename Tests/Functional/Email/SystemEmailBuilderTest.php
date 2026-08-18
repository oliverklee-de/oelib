<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Email;

use OliverKlee\Oelib\Email\GeneralEmailRole;
use OliverKlee\Oelib\Email\SystemEmailBuilder;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Oelib\Email\SystemEmailBuilder
 */
final class SystemEmailBuilderTest extends FunctionalTestCase
{
    protected bool $initializeDatabase = false;

    protected array $testExtensionsToLoad = ['oliverklee/oelib'];

    private SystemEmailBuilder $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new SystemEmailBuilder();
    }

    #[Test]
    public function isAvailableViaContainer(): void
    {
        $instance = $this->get(SystemEmailBuilder::class);

        self::assertInstanceOf(SystemEmailBuilder::class, $instance);
    }

    #[Test]
    public function buildWithConfiguredFromAddressReturnsGeneralEmailRoleWithDefaultFromAddress(): void
    {
        self::assertIsArray($GLOBALS['TYPO3_CONF_VARS']);
        self::assertIsArray($GLOBALS['TYPO3_CONF_VARS']['MAIL']);
        $fromAddress = 'someone@example.com';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = $fromAddress;

        $result = $this->subject->build();

        self::assertInstanceOf(GeneralEmailRole::class, $result);
        self::assertSame($fromAddress, $result->getEmailAddress());
    }

    #[Test]
    public function buildWithoutConfiguredFromAddressReturnsGeneralEmailRoleWithNoReplyAddress(): void
    {
        self::assertIsArray($GLOBALS['TYPO3_CONF_VARS']);
        self::assertIsArray($GLOBALS['TYPO3_CONF_VARS']['MAIL']);
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = '';

        $result = $this->subject->build();

        self::assertInstanceOf(GeneralEmailRole::class, $result);
        self::assertStringStartsWith('no-reply@', $result->getEmailAddress());
    }

    #[Test]
    public function buildWithConfiguredFromNameReturnsGeneralEmailRoleWithDefaultFromName(): void
    {
        self::assertIsArray($GLOBALS['TYPO3_CONF_VARS']);
        self::assertIsArray($GLOBALS['TYPO3_CONF_VARS']['MAIL']);
        $fromName = 'Dungeon Crawler Carl';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = $fromName;

        $result = $this->subject->build();

        self::assertInstanceOf(GeneralEmailRole::class, $result);
        self::assertSame($fromName, $result->getName());
    }

    #[Test]
    public function buildWithoutConfiguredFromNameReturnsGeneralEmailRoleWithEmptyFromName(): void
    {
        self::assertIsArray($GLOBALS['TYPO3_CONF_VARS']);
        self::assertIsArray($GLOBALS['TYPO3_CONF_VARS']['MAIL']);
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = '';

        $result = $this->subject->build();

        self::assertInstanceOf(GeneralEmailRole::class, $result);
        self::assertSame('', $result->getName());
    }
}
