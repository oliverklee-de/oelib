<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Model;

use OliverKlee\Oelib\Model\Currency;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Oelib\Model\Currency
 */
final class CurrencyTest extends UnitTestCase
{
    private Currency $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new Currency();
    }

    #[Test]
    public function getIsoAlpha3CodeReturnsIsoAlpha3Code(): void
    {
        $code = 'EUR';
        $this->subject->setData(['cu_iso_3' => $code]);

        self::assertSame($code, $this->subject->getIsoAlpha3Code());
    }

    #[Test]
    public function hasLeftSymbolForCurrencyWithLeftSymbolReturnsTrue(): void
    {
        $this->subject->setData(['cu_symbol_left' => '€']);

        self::assertTrue($this->subject->hasLeftSymbol());
    }

    #[Test]
    public function hasLeftSymbolForCurrencyWithoutLeftSymbolReturnsFalse(): void
    {
        $this->subject->setData(['cu_symbol_left' => '']);

        self::assertFalse($this->subject->hasLeftSymbol());
    }

    #[Test]
    public function getLeftSymbolByDefaultReturnsEmptyString(): void
    {
        $this->subject->setData([]);

        self::assertSame('', $this->subject->getLeftSymbol());
    }

    #[Test]
    public function getLeftSymbolByDefaultReturnsLeftSymbol(): void
    {
        $symbol = '€';
        $this->subject->setData(['cu_symbol_left' => $symbol]);

        self::assertSame($symbol, $this->subject->getLeftSymbol());
    }

    #[Test]
    public function hasRightSymbolForCurrencyWithRightSymbolReturnsTrue(): void
    {
        $this->subject->setData(['cu_symbol_right' => '€']);

        self::assertTrue($this->subject->hasRightSymbol());
    }

    #[Test]
    public function hasRightSymbolForCurrencyWithoutRightSymbolReturnsFalse(): void
    {
        $this->subject->setData(['cu_symbol_right' => '']);

        self::assertFalse($this->subject->hasRightSymbol());
    }

    #[Test]
    public function getRightSymbolByDefaultReturnsEmptyString(): void
    {
        $this->subject->setData([]);

        self::assertSame('', $this->subject->getRightSymbol());
    }

    #[Test]
    public function getRightSymbolByDefaultReturnsRightSymbol(): void
    {
        $symbol = '€';
        $this->subject->setData(['cu_symbol_right' => $symbol]);

        self::assertSame($symbol, $this->subject->getRightSymbol());
    }

    #[Test]
    public function getThousandsSeparatorReturnsThousandsSeparator(): void
    {
        $separator = '.';
        $this->subject->setData(['cu_thousands_point' => $separator]);

        self::assertSame($separator, $this->subject->getThousandsSeparator());
    }

    #[Test]
    public function getDecimalSeparatorReturnsDecimalSeparator(): void
    {
        $separator = ',';
        $this->subject->setData(['cu_decimal_point' => $separator]);

        self::assertSame($separator, $this->subject->getDecimalSeparator());
    }

    #[Test]
    public function getDecimalDigitsReturnsDecimalDigits(): void
    {
        $digits = 2;
        $this->subject->setData(['cu_decimal_digits' => (string)$digits]);

        self::assertSame($digits, $this->subject->getDecimalDigits());
    }
}
