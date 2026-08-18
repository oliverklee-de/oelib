<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Configuration;

use OliverKlee\Oelib\Configuration\PageFinder;
use OliverKlee\Oelib\Testing\TestingFramework;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Oelib\Configuration\PageFinder
 */
final class PageFinderTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = ['oliverklee/oelib'];

    private TestingFramework $testingFramework;

    private PageFinder $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testingFramework = $this->get(TestingFramework::class);

        $this->subject = PageFinder::getInstance();
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUpWithoutDatabase();
        PageFinder::purgeInstance();
        parent::tearDown();
    }

    ////////////////////////////////
    // Tests concerning getPageUid
    ////////////////////////////////

    #[Test]
    public function getPageUidWithFrontEndPageUidReturnsFrontEndPageUid(): void
    {
        $frontEndPageUid = $this->testingFramework->createFrontEndPage();
        $this->testingFramework->createFakeFrontEnd($frontEndPageUid);

        self::assertSame($frontEndPageUid, $this->subject->getPageUid());
    }

    #[Test]
    public function getPageUidWithoutFrontEndAndWithBackendPageUidReturnsBackEndPageUid(): void
    {
        $_GET['id'] = 42;

        $pageUid = $this->subject->getPageUid();
        unset($_GET['id']);

        self::assertSame(
            42,
            $pageUid,
        );
    }

    #[Test]
    public function getPageUidWithFrontEndAndBackendPageUidReturnsFrontEndPageUid(): void
    {
        $frontEndPageUid = $this->testingFramework->createFrontEndPage();
        $this->testingFramework->createFakeFrontEnd($frontEndPageUid);
        $_GET['id'] = $frontEndPageUid + 1;

        $pageUid = $this->subject->getPageUid();

        unset($_GET['id']);

        self::assertSame(
            $frontEndPageUid,
            $pageUid,
        );
    }

    #[Test]
    public function getPageUidForManuallySetPageUidAndSetFrontEndPageUidReturnsManuallySetPageUid(): void
    {
        $frontEndPageUid = $this->testingFramework->createFrontEndPage();
        $this->testingFramework->createFakeFrontEnd($frontEndPageUid);
        $this->subject->setPageUid($frontEndPageUid + 1);

        self::assertSame(
            $frontEndPageUid + 1,
            $this->subject->getPageUid(),
        );
    }

    /////////////////////////////////
    // Tests concerning forceSource
    /////////////////////////////////

    #[Test]
    public function forceSourceWithSourceSetToFrontEndAndManuallySetPageUidReturnsFrontEndPageUid(): void
    {
        $this->subject->forceSource(PageFinder::SOURCE_FRONT_END);
        $frontEndPageUid = $this->testingFramework->createFrontEndPage();
        $this->testingFramework->createFakeFrontEnd($frontEndPageUid);

        $this->subject->setPageUid($frontEndPageUid + 1);

        self::assertSame(
            $frontEndPageUid,
            $this->subject->getPageUid(),
        );
    }

    #[Test]
    public function forceSourceWithSourceSetToBackEndAndSetFrontEndUidReturnsBackEndEndPageUid(): void
    {
        $this->subject->forceSource(PageFinder::SOURCE_BACK_END);
        $frontEndPageUid = $this->testingFramework->createFrontEndPage();
        $this->testingFramework->createFakeFrontEnd($frontEndPageUid);

        $_GET['id'] = $frontEndPageUid + 1;
        $pageUid = $this->subject->getPageUid();
        unset($_GET['id']);

        self::assertSame($frontEndPageUid + 1, $pageUid);
    }

    #[Test]
    public function forceSourceWithSourceSetToFrontEndAndManuallySetPageUidButNoFrontEndUidSetReturnsZero(): void
    {
        $this->subject->forceSource(PageFinder::SOURCE_FRONT_END);

        $this->subject->setPageUid(15);

        self::assertSame(
            0,
            $this->subject->getPageUid(),
        );
    }

    //////////////////////////////////////
    // Tests concerning getCurrentSource
    //////////////////////////////////////

    #[Test]
    public function getCurrentSourceForNoSourceForcedAndNoPageUidSetReturnsNoSourceFound(): void
    {
        self::assertSame(
            PageFinder::NO_SOURCE_FOUND,
            $this->subject->getCurrentSource(),
        );
    }

    #[Test]
    public function getCurrentSourceForSourceForcedToFrontEndReturnsSourceFrontEnd(): void
    {
        $this->subject->forceSource(PageFinder::SOURCE_FRONT_END);

        self::assertSame(
            PageFinder::SOURCE_FRONT_END,
            $this->subject->getCurrentSource(),
        );
    }

    #[Test]
    public function getCurrentSourceForSourceForcedToBackEndReturnsSourceBackEnd(): void
    {
        $this->subject->forceSource(PageFinder::SOURCE_BACK_END);

        self::assertSame(
            PageFinder::SOURCE_BACK_END,
            $this->subject->getCurrentSource(),
        );
    }

    #[Test]
    public function getCurrentSourceForManuallySetPageIdReturnsSourceManual(): void
    {
        $this->subject->setPageUid(42);

        self::assertSame(
            PageFinder::SOURCE_MANUAL,
            $this->subject->getCurrentSource(),
        );
    }

    #[Test]
    public function getCurrentSourceForSetFrontEndPageUidReturnsSourceFrontEnd(): void
    {
        $frontEndPageUid = $this->testingFramework->createFrontEndPage();
        $this->testingFramework->createFakeFrontEnd($frontEndPageUid);

        self::assertSame(
            PageFinder::SOURCE_FRONT_END,
            $this->subject->getCurrentSource(),
        );
    }

    #[Test]
    public function getCurrentSourceForSetBackEndPageUidReturnsSourceBackEnd(): void
    {
        $_GET['id'] = 42;
        $pageSource = $this->subject->getCurrentSource();
        unset($_GET['id']);

        self::assertSame(
            PageFinder::SOURCE_BACK_END,
            $pageSource,
        );
    }
}
