<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Configuration;

use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\Configuration\FallbackConfiguration;
use OliverKlee\Oelib\Interfaces\Configuration;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Oelib\Configuration\FallbackConfiguration
 */
final class FallbackConfigurationTest extends UnitTestCase
{
    #[Test]
    public function implementsConfiguration(): void
    {
        $subject = new FallbackConfiguration(new DummyConfiguration(), new DummyConfiguration());

        self::assertInstanceOf(Configuration::class, $subject);
    }

    #[Test]
    public function hasSourceNameFromBothConfigurations(): void
    {
        $primarySourceName = 'primary';
        $primary = new DummyConfiguration();
        $primary->setSourceName($primarySourceName);

        $secondarySourceName = 'secondary';
        $secondary = new DummyConfiguration();
        $secondary->setSourceName($secondarySourceName);

        $subject = new FallbackConfiguration($primary, $secondary);

        $expected = $primarySourceName . ' or ' . $secondarySourceName;
        self::assertSame($expected, $subject->getSourceName());
    }

    #[Test]
    public function getAsStringForBothEmptyStringReturnsEmptyString(): void
    {
        $key = 'something';
        $primary = new DummyConfiguration([$key => '']);
        $secondary = new DummyConfiguration([$key => '']);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertSame('', $subject->getAsString($key));
    }

    #[Test]
    public function getAsStringForBothNonEmptyReturnsValueFromPrimary(): void
    {
        $key = 'something';
        $primaryValue = 'primary';
        $primary = new DummyConfiguration([$key => $primaryValue]);
        $secondaryValue = 'secondary';
        $secondary = new DummyConfiguration([$key => $secondaryValue]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertSame($primaryValue, $subject->getAsString($key));
    }

    #[Test]
    public function getAsStringForPrimaryNonEmptyAndSecondaryEmptyReturnsValueFromPrimary(): void
    {
        $key = 'something';
        $primaryValue = 'primary';
        $primary = new DummyConfiguration([$key => $primaryValue]);
        $secondaryValue = '';
        $secondary = new DummyConfiguration([$key => $secondaryValue]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertSame($primaryValue, $subject->getAsString($key));
    }

    #[Test]
    public function getAsStringForPrimaryEmptyAndSecondaryNonEmptyReturnsValueFromSecondary(): void
    {
        $key = 'something';
        $primaryValue = '';
        $primary = new DummyConfiguration([$key => $primaryValue]);
        $secondaryValue = 'secondary';
        $secondary = new DummyConfiguration([$key => $secondaryValue]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertSame($secondaryValue, $subject->getAsString($key));
    }

    #[Test]
    public function hasStringForBothEmptyStringReturnsFalse(): void
    {
        $key = 'something';
        $primary = new DummyConfiguration([$key => '']);
        $secondary = new DummyConfiguration([$key => '']);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertFalse($subject->hasString($key));
    }

    #[Test]
    public function hasStringForBothNonEmptyReturnsTrue(): void
    {
        $key = 'something';
        $primary = new DummyConfiguration([$key => 'primary']);
        $secondary = new DummyConfiguration([$key => 'secondary']);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertTrue($subject->hasString($key));
    }

    #[Test]
    public function hasStringForPrimaryNonEmptyAndSecondaryEmptyReturnsTrue(): void
    {
        $key = 'something';
        $primary = new DummyConfiguration([$key => 'primary']);
        $secondary = new DummyConfiguration([$key => '']);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertTrue($subject->hasString($key));
    }

    #[Test]
    public function hasStringForPrimaryEmptyAndSecondaryNonEmptyReturnsTrue(): void
    {
        $key = 'something';
        $primary = new DummyConfiguration([$key => '']);
        $secondary = new DummyConfiguration([$key => 'secondary']);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertTrue($subject->hasString($key));
    }

    #[Test]
    public function getAsIntegerForBothZeroReturnsZero(): void
    {
        $key = 'something';
        $primary = new DummyConfiguration([$key => 0]);
        $secondary = new DummyConfiguration([$key => 0]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertSame(0, $subject->getAsInteger($key));
    }

    #[Test]
    public function getAsIntegerForBothNonZeroReturnsValueFromPrimary(): void
    {
        $key = 'something';
        $primaryValue = 1;
        $primary = new DummyConfiguration([$key => $primaryValue]);
        $secondaryValue = 2;
        $secondary = new DummyConfiguration([$key => $secondaryValue]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertSame($primaryValue, $subject->getAsInteger($key));
    }

    #[Test]
    public function getAsIntegerForPrimaryNonZeroAndSecondaryZeroReturnsValueFromPrimary(): void
    {
        $key = 'something';
        $primaryValue = 1;
        $primary = new DummyConfiguration([$key => $primaryValue]);
        $secondaryValue = 0;
        $secondary = new DummyConfiguration([$key => $secondaryValue]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertSame($primaryValue, $subject->getAsInteger($key));
    }

    #[Test]
    public function getAsIntegerForPrimaryZeroAndSecondaryNonZeroReturnsValueFromSecondary(): void
    {
        $key = 'something';
        $primaryValue = 0;
        $primary = new DummyConfiguration([$key => $primaryValue]);
        $secondaryValue = 2;
        $secondary = new DummyConfiguration([$key => $secondaryValue]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertSame($secondaryValue, $subject->getAsInteger($key));
    }

    #[Test]
    public function hasIntegerForBothZeroReturnsFalse(): void
    {
        $key = 'something';
        $primary = new DummyConfiguration([$key => 0]);
        $secondary = new DummyConfiguration([$key => 0]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertFalse($subject->hasInteger($key));
    }

    #[Test]
    public function hasIntegerForBothNonZeroReturnsTrue(): void
    {
        $key = 'something';
        $primary = new DummyConfiguration([$key => 1]);
        $secondary = new DummyConfiguration([$key => 2]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertTrue($subject->hasInteger($key));
    }

    #[Test]
    public function hasIntegerForPrimaryNonZeroAndSecondaryZeroReturnsTrue(): void
    {
        $key = 'something';
        $primary = new DummyConfiguration([$key => 1]);
        $secondary = new DummyConfiguration([$key => 0]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertTrue($subject->hasInteger($key));
    }

    #[Test]
    public function hasIntegerForPrimaryZeroAndSecondaryNonZeroReturnsTrue(): void
    {
        $key = 'something';
        $primary = new DummyConfiguration([$key => 0]);
        $secondary = new DummyConfiguration([$key => 2]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertTrue($subject->hasInteger($key));
    }

    #[Test]
    public function getAsNonNegativeIntegerForBothZeroReturnsZero(): void
    {
        $key = 'something';
        $primary = new DummyConfiguration([$key => 0]);
        $secondary = new DummyConfiguration([$key => 0]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertSame(0, $subject->getAsNonNegativeInteger($key));
    }

    #[Test]
    public function getAsNonNegativeIntegerForBothPositiveReturnsValueFromPrimary(): void
    {
        $key = 'something';
        $primaryValue = 1;
        $primary = new DummyConfiguration([$key => $primaryValue]);
        $secondaryValue = 2;
        $secondary = new DummyConfiguration([$key => $secondaryValue]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertSame($primaryValue, $subject->getAsNonNegativeInteger($key));
    }

    #[Test]
    public function getAsNonNegativeIntegerForPrimaryPositiveAndSecondaryZeroReturnsValueFromPrimary(): void
    {
        $key = 'something';
        $primaryValue = 1;
        $primary = new DummyConfiguration([$key => $primaryValue]);
        $secondaryValue = 0;
        $secondary = new DummyConfiguration([$key => $secondaryValue]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertSame($primaryValue, $subject->getAsNonNegativeInteger($key));
    }

    #[Test]
    public function getAsNonNegativeIntegerForPrimaryPositiveAndSecondaryNegativeReturnsValueFromPrimary(): void
    {
        $key = 'something';
        $primaryValue = 1;
        $primary = new DummyConfiguration([$key => $primaryValue]);
        $secondaryValue = -1;
        $secondary = new DummyConfiguration([$key => $secondaryValue]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertSame($primaryValue, $subject->getAsNonNegativeInteger($key));
    }

    #[Test]
    public function getAsNonNegativeIntegerForPrimaryZeroAndSecondaryPositiveReturnsValueFromSecondary(): void
    {
        $key = 'something';
        $primaryValue = 0;
        $primary = new DummyConfiguration([$key => $primaryValue]);
        $secondaryValue = 2;
        $secondary = new DummyConfiguration([$key => $secondaryValue]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertSame($secondaryValue, $subject->getAsNonNegativeInteger($key));
    }

    #[Test]
    public function getAsNonNegativeIntegerForPrimaryNegativeAndSecondaryPositiveThrowsException(): void
    {
        $key = 'something';
        $primaryValue = -1;
        $primary = new DummyConfiguration([$key => $primaryValue]);
        $secondaryValue = 1;
        $secondary = new DummyConfiguration([$key => $secondaryValue]);
        $subject = new FallbackConfiguration($primary, $secondary);

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('The value for "something" must be a non-negative integer, but it is -1.');
        $this->expectExceptionCode(1573030133);

        $subject->getAsNonNegativeInteger($key);
    }

    #[Test]
    public function getAsNonNegativeIntegerForBothNegativeThrowsException(): void
    {
        $key = 'something';
        $primaryValue = -1;
        $primary = new DummyConfiguration([$key => $primaryValue]);
        $secondaryValue = -1;
        $secondary = new DummyConfiguration([$key => $secondaryValue]);
        $subject = new FallbackConfiguration($primary, $secondary);

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('The value for "something" must be a non-negative integer, but it is -1.');
        $this->expectExceptionCode(1573030133);

        $subject->getAsNonNegativeInteger($key);
    }

    #[Test]
    public function getAsNonNegativeIntegerForPrimaryZeroAndSecondaryNegativeThrowsException(): void
    {
        $key = 'something';
        $primaryValue = 0;
        $primary = new DummyConfiguration([$key => $primaryValue]);
        $secondaryValue = -1;
        $secondary = new DummyConfiguration([$key => $secondaryValue]);
        $subject = new FallbackConfiguration($primary, $secondary);

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('The value for "something" must be a non-negative integer, but it is -1.');
        $this->expectExceptionCode(1573030133);

        $subject->getAsNonNegativeInteger($key);
    }

    #[Test]
    public function getAsPositiveIntegerForBothZeroThrowsException(): void
    {
        $key = 'something';
        $primary = new DummyConfiguration([$key => 0]);
        $secondary = new DummyConfiguration([$key => 0]);
        $subject = new FallbackConfiguration($primary, $secondary);

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('The value for "something" must be a positive integer, but it is 0.');
        $this->expectExceptionCode(1573030133);

        $subject->getAsPositiveInteger($key);
    }

    #[Test]
    public function getAsPositiveIntegerForBothPositiveReturnsValueFromPrimary(): void
    {
        $key = 'something';
        $primaryValue = 1;
        $primary = new DummyConfiguration([$key => $primaryValue]);
        $secondaryValue = 2;
        $secondary = new DummyConfiguration([$key => $secondaryValue]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertSame($primaryValue, $subject->getAsPositiveInteger($key));
    }

    #[Test]
    public function getAsPositiveIntegerForPrimaryPositiveAndSecondaryZeroReturnsValueFromPrimary(): void
    {
        $key = 'something';
        $primaryValue = 1;
        $primary = new DummyConfiguration([$key => $primaryValue]);
        $secondaryValue = 0;
        $secondary = new DummyConfiguration([$key => $secondaryValue]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertSame($primaryValue, $subject->getAsPositiveInteger($key));
    }

    #[Test]
    public function getAsPositiveIntegerForPrimaryPositiveAndSecondaryNegativeReturnsValueFromPrimary(): void
    {
        $key = 'something';
        $primaryValue = 1;
        $primary = new DummyConfiguration([$key => $primaryValue]);
        $secondaryValue = -1;
        $secondary = new DummyConfiguration([$key => $secondaryValue]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertSame($primaryValue, $subject->getAsPositiveInteger($key));
    }

    #[Test]
    public function getAsPositiveIntegerForPrimaryNegativeAndSecondaryPositiveThrowsException(): void
    {
        $key = 'something';
        $primaryValue = -1;
        $primary = new DummyConfiguration([$key => $primaryValue]);
        $secondaryValue = 1;
        $secondary = new DummyConfiguration([$key => $secondaryValue]);
        $subject = new FallbackConfiguration($primary, $secondary);

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('The value for "something" must be a positive integer, but it is -1.');
        $this->expectExceptionCode(1573030133);

        $subject->getAsPositiveInteger($key);
    }

    #[Test]
    public function getAsPositiveIntegerForPrimaryZeroAndSecondaryNegativeThrowsException(): void
    {
        $key = 'something';
        $primaryValue = 0;
        $primary = new DummyConfiguration([$key => $primaryValue]);
        $secondaryValue = -1;
        $secondary = new DummyConfiguration([$key => $secondaryValue]);
        $subject = new FallbackConfiguration($primary, $secondary);

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('The value for "something" must be a positive integer, but it is -1.');
        $this->expectExceptionCode(1573030133);

        $subject->getAsPositiveInteger($key);
    }

    #[Test]
    public function getAsPositiveIntegerForPrimaryZeroAndSecondaryPositiveReturnsValueFromSecondary(): void
    {
        $key = 'something';
        $primaryValue = 0;
        $primary = new DummyConfiguration([$key => $primaryValue]);
        $secondaryValue = 2;
        $secondary = new DummyConfiguration([$key => $secondaryValue]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertSame($secondaryValue, $subject->getAsPositiveInteger($key));
    }

    #[Test]
    public function getAsBooleanForBothFalseReturnsFalse(): void
    {
        $key = 'something';
        $primary = new DummyConfiguration([$key => false]);
        $secondary = new DummyConfiguration([$key => false]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertFalse($subject->getAsBoolean($key));
    }

    #[Test]
    public function getAsBooleanForBothTrueReturnsTrue(): void
    {
        $key = 'something';
        $primary = new DummyConfiguration([$key => true]);
        $secondary = new DummyConfiguration([$key => true]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertTrue($subject->getAsBoolean($key));
    }

    #[Test]
    public function getAsBooleanForPrimaryTrueAndSecondaryFalseReturnsTrue(): void
    {
        $key = 'something';
        $primary = new DummyConfiguration([$key => true]);
        $secondary = new DummyConfiguration([$key => false]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertTrue($subject->getAsBoolean($key));
    }

    #[Test]
    public function getAsBooleanForPrimaryFalseAndSecondaryTrueReturnsTrue(): void
    {
        $key = 'something';
        $primary = new DummyConfiguration([$key => false]);
        $secondary = new DummyConfiguration([$key => true]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertTrue($subject->getAsBoolean($key));
    }

    #[Test]
    public function getAsTrimmedArrayForBothEmptyArrayReturnsEmptyArray(): void
    {
        $key = 'something';
        $primary = new DummyConfiguration([$key => '']);
        $secondary = new DummyConfiguration([$key => '']);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertSame([], $subject->getAsTrimmedArray($key));
    }

    #[Test]
    public function getAsTrimmedArrayForBothNonEmptyReturnsValueFromPrimary(): void
    {
        $key = 'something';
        $primaryValue = 'primary';
        $primary = new DummyConfiguration([$key => $primaryValue]);
        $secondaryValue = 'secondary';
        $secondary = new DummyConfiguration([$key => $secondaryValue]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertSame([$primaryValue], $subject->getAsTrimmedArray($key));
    }

    #[Test]
    public function getAsTrimmedArrayForPrimaryNonEmptyAndSecondaryEmptyReturnsValueFromPrimary(): void
    {
        $key = 'something';
        $primaryValue = 'primary';
        $primary = new DummyConfiguration([$key => $primaryValue]);
        $secondaryValue = '';
        $secondary = new DummyConfiguration([$key => $secondaryValue]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertSame([$primaryValue], $subject->getAsTrimmedArray($key));
    }

    #[Test]
    public function getAsTrimmedArrayForPrimaryEmptyAndSecondaryNonEmptyReturnsValueFromSecondary(): void
    {
        $key = 'something';
        $primaryValue = '';
        $primary = new DummyConfiguration([$key => $primaryValue]);
        $secondaryValue = 'secondary';
        $secondary = new DummyConfiguration([$key => $secondaryValue]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertSame([$secondaryValue], $subject->getAsTrimmedArray($key));
    }

    #[Test]
    public function getAsTrimmedArrayTrimsValues(): void
    {
        $key = 'something';
        $primaryValue = 'primary';
        $primary = new DummyConfiguration([$key => sprintf(' %s ', $primaryValue)]);
        $secondaryValue = '';
        $secondary = new DummyConfiguration([$key => $secondaryValue]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertSame([$primaryValue], $subject->getAsTrimmedArray($key));
    }

    #[Test]
    public function getAsTrimmedArrayExplodesValues(): void
    {
        $key = 'something';
        $primaryValue1 = 'primary 1';
        $primaryValue2 = 'primary 2';
        $primary = new DummyConfiguration([$key => sprintf('%s, %s', $primaryValue1, $primaryValue2)]);
        $secondaryValue = '';
        $secondary = new DummyConfiguration([$key => $secondaryValue]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertSame([$primaryValue1, $primaryValue2], $subject->getAsTrimmedArray($key));
    }
}
