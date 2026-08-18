<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Configuration;

use OliverKlee\Oelib\Configuration\PageFinder;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Oelib\Configuration\PageFinder
 */
final class PageFinderTest extends UnitTestCase
{
    private PageFinder $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = PageFinder::getInstance();
    }

    ////////////////////////////////////////////
    // Tests concerning the Singleton property
    ////////////////////////////////////////////

    #[Test]
    public function getInstanceReturnsPageFinderInstance(): void
    {
        self::assertInstanceOf(
            PageFinder::class,
            PageFinder::getInstance(),
        );
    }

    #[Test]
    public function getInstanceTwoTimesReturnsSameInstance(): void
    {
        self::assertSame(
            PageFinder::getInstance(),
            PageFinder::getInstance(),
        );
    }

    #[Test]
    public function getInstanceAfterPurgeInstanceReturnsNewInstance(): void
    {
        $firstInstance = PageFinder::getInstance();
        PageFinder::purgeInstance();

        self::assertNotSame(
            $firstInstance,
            PageFinder::getInstance(),
        );
    }

    ////////////////////////////////
    // tests concerning setPageUid
    ////////////////////////////////

    #[Test]
    public function getPageUidWithSetPageUidViaSetPageUidReturnsSetPageUid(): void
    {
        $this->subject->setPageUid(42);

        self::assertSame(
            42,
            $this->subject->getPageUid(),
        );
    }

    #[Test]
    public function setPageUidWithZeroGivenThrowsException(): void
    {
        $this->expectException(
            \InvalidArgumentException::class,
        );
        $this->expectExceptionMessage(
            'The given page UID was "0". Only integer values greater than zero are allowed.',
        );

        // @phpstan-ignore-next-line We're testing for a contract violation here.
        $this->subject->setPageUid(0);
    }

    #[Test]
    public function setPageUidWithNegativeNumberGivenThrowsException(): void
    {
        $this->expectException(
            \InvalidArgumentException::class,
        );
        $this->expectExceptionMessage(
            'The given page UID was "-21". Only integer values greater than zero are allowed.',
        );

        // @phpstan-ignore-next-line We're testing for a contract violation here.
        $this->subject->setPageUid(-21);
    }
}
