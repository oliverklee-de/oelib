<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Configuration;

use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\DataStructures\AbstractObjectWithPublicAccessors;
use OliverKlee\Oelib\Interfaces\Configuration as ConfigurationInterface;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Oelib\Configuration\DummyConfiguration
 */
final class DummyConfigurationTest extends UnitTestCase
{
    private DummyConfiguration $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new DummyConfiguration();
    }

    #[Test]
    public function implementsConfigurationInterface(): void
    {
        self::assertInstanceOf(ConfigurationInterface::class, $this->subject);
    }

    #[Test]
    public function byDefaultHasDummySourceName(): void
    {
        $subject = new DummyConfiguration();

        self::assertSame('dummy configuration for testing', $subject->getSourceName());
    }

    #[Test]
    public function canOverwriteSourceName(): void
    {
        $sourceName = 'set via setter';
        $subject = new DummyConfiguration();

        $subject->setSourceName($sourceName);

        self::assertSame($sourceName, $subject->getSourceName());
    }

    #[Test]
    public function isObjectWithPublicAccessors(): void
    {
        self::assertInstanceOf(AbstractObjectWithPublicAccessors::class, $this->subject);
    }

    #[Test]
    public function hasEmptyStringAsDefaultValueForInexistentString(): void
    {
        self::assertSame('', $this->subject->getAsString('nothing'));
    }

    #[Test]
    public function hasZeroAsDefaultValueForInexistentInteger(): void
    {
        self::assertSame(0, $this->subject->getAsInteger('nothing'));
    }

    #[Test]
    public function hasFalseAsDefaultValueForInexistentBoolean(): void
    {
        self::assertFalse($this->subject->getAsBoolean('nothing'));
    }

    #[Test]
    public function canProvideDataViaConstructor(): void
    {
        $key = 'name';
        $value = 'Max';
        $subject = new DummyConfiguration([$key => $value]);

        self::assertSame($value, $subject->getAsString($key));
    }

    #[Test]
    public function canGetString(): void
    {
        $key = 'name';
        $value = 'Max';
        $this->subject->setAllData([$key => $value]);

        self::assertSame($value, $this->subject->getAsString($key));
    }

    #[Test]
    public function canGetInteger(): void
    {
        $key = 'size';
        $value = 12;
        $this->subject->setAllData([$key => $value]);

        self::assertSame($value, $this->subject->getAsInteger($key));
    }

    #[Test]
    public function canGetBoolean(): void
    {
        $key = 'isActive';
        $this->subject->setAllData([$key => true]);

        self::assertTrue($this->subject->getAsBoolean($key));
    }

    #[Test]
    public function canSetString(): void
    {
        $key = 'name';
        $value = 'Max';

        $this->subject->setAsString($key, $value);

        self::assertSame($value, $this->subject->getAsString($key));
    }

    #[Test]
    public function canSetInteger(): void
    {
        $key = 'size';
        $value = 12;

        $this->subject->setAsInteger($key, $value);

        self::assertSame($value, $this->subject->getAsInteger($key));
    }

    #[Test]
    public function canSetBoolean(): void
    {
        $key = 'isActive';
        $this->subject->setAsBoolean($key, true);

        self::assertTrue($this->subject->getAsBoolean($key));
    }
}
