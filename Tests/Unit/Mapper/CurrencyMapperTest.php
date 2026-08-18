<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Mapper;

use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Oelib\Mapper\CurrencyMapper;
use OliverKlee\Oelib\Model\Currency;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Oelib\Mapper\CurrencyMapper
 */
final class CurrencyMapperTest extends UnitTestCase
{
    private CurrencyMapper $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new CurrencyMapper();
    }

    #[Test]
    public function isMapper(): void
    {
        self::assertInstanceOf(AbstractDataMapper::class, $this->subject);
    }

    #[Test]
    public function createsCurrencyModel(): void
    {
        $model = $this->subject->getNewGhost();

        self::assertInstanceOf(Currency::class, $model);
    }
}
