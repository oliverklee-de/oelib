<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Domain\Model;

use OliverKlee\Oelib\Domain\Model\GermanZipCode;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Oelib\Domain\Model\GermanZipCode
 */
final class GermanZipCodeTest extends UnitTestCase
{
    private GermanZipCode $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new GermanZipCode();
    }

    #[Test]
    public function isAbstractEntity(): void
    {
        self::assertInstanceOf(AbstractEntity::class, $this->subject);
    }

    #[Test]
    public function getZipCodeInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getZipCode());
    }

    #[Test]
    public function setZipCodeSetsZipCode(): void
    {
        $value = '01234';
        $this->subject->setZipCode($value);

        self::assertSame($value, $this->subject->getZipCode());
    }

    #[Test]
    public function getCityNameInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getCityName());
    }

    #[Test]
    public function setCityNameSetsCityName(): void
    {
        $value = 'Köln';
        $this->subject->setCityName($value);

        self::assertSame($value, $this->subject->getCityName());
    }

    #[Test]
    public function getLongitudeInitiallyReturnsZero(): void
    {
        self::assertSame(0.0, $this->subject->getLongitude());
    }

    #[Test]
    public function setLongitudeSetsLongitude(): void
    {
        $value = 1234.56;
        $this->subject->setLongitude($value);

        self::assertSame($value, $this->subject->getLongitude());
    }

    #[Test]
    public function getLatitudeInitiallyReturnsZero(): void
    {
        self::assertSame(0.0, $this->subject->getLatitude());
    }

    #[Test]
    public function setLatitudeSetsLatitude(): void
    {
        $value = 1234.56;
        $this->subject->setLatitude($value);

        self::assertSame($value, $this->subject->getLatitude());
    }
}
