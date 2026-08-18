<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\ViewHelpers;

use OliverKlee\Oelib\ViewHelpers\PriceViewHelper;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface;

/**
 * @covers \OliverKlee\Oelib\ViewHelpers\PriceViewHelper
 */
class PriceViewHelperTest extends UnitTestCase
{
    private PriceViewHelper $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new PriceViewHelper();
    }

    #[Test]
    public function isViewHelper(): void
    {
        self::assertInstanceOf(AbstractViewHelper::class, $this->subject);
    }

    #[Test]
    public function implementsViewHelper(): void
    {
        self::assertInstanceOf(ViewHelperInterface::class, $this->subject);
    }

    #[Test]
    public function renderWithoutSettingValueOrCurrencyFirstRendersZeroWithTwoDigits(): void
    {
        self::assertSame(
            '0.00',
            $this->subject->render(),
        );
    }

    #[Test]
    public function renderWithValueWithoutSettingCurrencyUsesDecimalPointAndTwoDecimalDigits(): void
    {
        $this->subject->setValue(12345.678);

        self::assertSame(
            '12345.68',
            $this->subject->render(),
        );
    }
}
