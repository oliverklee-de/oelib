<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\DataStructures;

use OliverKlee\Oelib\Tests\Unit\DataStructures\Fixtures\TestingReadOnlyObjectWithPublicAccessors;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Oelib\DataStructures\AbstractReadOnlyObjectWithAccessors
 * @covers \OliverKlee\Oelib\DataStructures\AbstractReadOnlyObjectWithPublicAccessors
 */
final class AbstractReadOnlyObjectWithAccessorsTest extends UnitTestCase
{
    private TestingReadOnlyObjectWithPublicAccessors $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new TestingReadOnlyObjectWithPublicAccessors();
    }

    /**
     * @test
     */
    public function checkForNonEmptyKeyWithEmptyKeyThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$key must not be empty.');
        $this->expectExceptionCode(1_331_488_963);

        $this->subject->checkForNonEmptyKey('');
    }

    /**
     * @test
     */
    public function checkForNonEmptyKeyWithNonEmptyKeyIsAllowed(): void
    {
        $this->subject->checkForNonEmptyKey('foo');
    }

    /**
     * @test
     */
    public function getAsStringWithEmptyKeyThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$key must not be empty.');
        $this->expectExceptionCode(1_331_488_963);

        // @phpstan-ignore-next-line We are explicitly checking for a contract violation here.
        $this->subject->getAsString('');
    }

    /**
     * @test
     */
    public function getAsStringWithInexistentKeyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getAsString('foo'));
    }

    /**
     * @return array<string, array{0: string|int|bool, 1: string}>
     */
    public static function stringDataProvider(): array
    {
        return [
            'empty string' => ['', ''],
            'non-empty string' => ['bar', 'bar'],
            'integer' => [1, '1'],
            'boolean true' => [true, '1'],
            'boolean false' => [false, ''],
        ];
    }

    /**
     * @test
     *
     * @dataProvider stringDataProvider
     */
    public function getAsStringReturnsDataCastToString(string|int|bool $inputValue, string $expected): void
    {
        $key = 'foo';
        $this->subject->setData([$key => $inputValue]);

        self::assertSame($expected, $this->subject->getAsString($key));
    }

    /**
     * @test
     */
    public function getAsStringReturnsTrimmedValue(): void
    {
        $key = 'foo';
        $this->subject->setData([$key => ' bar ']);

        self::assertSame('bar', $this->subject->getAsString($key));
    }

    /**
     * @test
     */
    public function getAsIntegerWithEmptyKeyThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$key must not be empty.');
        $this->expectExceptionCode(1_331_488_963);

        // @phpstan-ignore-next-line We are explicitly checking for a contract violation here.
        $this->subject->getAsInteger('');
    }

    /**
     * @test
     */
    public function getAsIntegerWithInexistentKeyReturnsZero(): void
    {
        self::assertSame(0, $this->subject->getAsInteger('foo'));
    }

    /**
     * @return array<string, array{0: int|string|float|bool, 1: int}>
     */
    public static function integerDataProvider(): array
    {
        return [
            'zero' => [0, 0],
            'positive integer' => [2, 2],
            'negative integer' => [-2, -2],
            'integer as string' => ['2', 2],
            'any other string' => ['bar', 0],
            'boolean true' => [true, 1],
            'float' => [12.34, 12],
        ];
    }

    /**
     * @test
     *
     * @dataProvider integerDataProvider
     */
    public function getAsIntegerReturnsDataCastToInteger(int|string|float|bool $inputValue, int $expected): void
    {
        $key = 'foo';
        $this->subject->setData([$key => $inputValue]);

        self::assertSame($expected, $this->subject->getAsInteger($key));
    }

    /**
     * @test
     */
    public function getAsNonNegativeIntegerWithEmptyKeyThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$key must not be empty.');
        $this->expectExceptionCode(1_331_488_963);

        // @phpstan-ignore-next-line We are explicitly checking for a contract violation here.
        $this->subject->getAsNonNegativeInteger('');
    }

    /**
     * @test
     */
    public function getAsPositiveIntegerWithEmptyKeyThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$key must not be empty.');
        $this->expectExceptionCode(1_331_488_963);

        // @phpstan-ignore-next-line We are explicitly checking for a contract violation here.
        $this->subject->getAsPositiveInteger('');
    }

    /**
     * @test
     */
    public function getAsNonNegativeIntegerForZeroReturnsSetValue(): void
    {
        $key = 'foo';
        $value = 0;
        $this->subject->setData([$key => $value]);

        self::assertSame($value, $this->subject->getAsNonNegativeInteger($key));
    }

    /**
     * @test
     */
    public function getAsNonNegativeIntegerForPositiveValueReturnsSetValue(): void
    {
        $key = 'foo';
        $value = 1;
        $this->subject->setData([$key => $value]);

        self::assertSame($value, $this->subject->getAsNonNegativeInteger($key));
    }

    /**
     * @test
     */
    public function getAsNonNegativeIntegerForPositiveStringValueReturnsSetIntegerValue(): void
    {
        $key = 'foo';
        $this->subject->setData([$key => '2']);

        self::assertSame(2, $this->subject->getAsNonNegativeInteger($key));
    }

    /**
     * @test
     */
    public function getAsNonNegativeIntegerForNegativeValueThrowsException(): void
    {
        $key = 'foo';
        $this->subject->setData([$key => -1]);

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('The value for the key "foo" must be a non-negative integer, but it is -1.');
        $this->expectExceptionCode(1735299608);

        $this->subject->getAsNonNegativeInteger($key);
    }

    /**
     * @test
     */
    public function getAsPositiveIntegerForPositiveIntegerValueReturnsSetValue(): void
    {
        $key = 'foo';
        $value = 1;
        $this->subject->setData([$key => $value]);

        self::assertSame($value, $this->subject->getAsPositiveInteger($key));
    }

    /**
     * @test
     */
    public function getAsPositiveIntegerForPositiveStringValueReturnsSetIntegerValue(): void
    {
        $key = 'foo';
        $this->subject->setData([$key => '2']);

        self::assertSame(2, $this->subject->getAsPositiveInteger($key));
    }

    /**
     * @test
     */
    public function getAsPositiveIntegerForZeroThrowsException(): void
    {
        $key = 'foo';
        $this->subject->setData([$key => 0]);

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('The value for the key "foo" must be a positive integer, but it is 0');
        $this->expectExceptionCode(1735299700);

        $this->subject->getAsPositiveInteger($key);
    }

    /**
     * @test
     */
    public function getAsPositiveIntegerForNegativeValueThrowsException(): void
    {
        $key = 'foo';
        $this->subject->setData([$key => -1]);

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('The value for the key "foo" must be a positive integer, but it is -1.');
        $this->expectExceptionCode(1735299700);

        $this->subject->getAsPositiveInteger($key);
    }

    /**
     * @test
     */
    public function getAsTrimmedArrayWithEmptyKeyThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$key must not be empty.');
        $this->expectExceptionCode(1_331_488_963);

        // @phpstan-ignore-next-line We are explicitly checking for a contract violation here.
        $this->subject->getAsTrimmedArray('');
    }

    /**
     * @test
     */
    public function getAsIntegerArrayWithEmptyKeyThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$key must not be empty.');
        $this->expectExceptionCode(1_331_488_963);

        // @phpstan-ignore-next-line We are explicitly checking for a contract violation here.
        $this->subject->getAsIntegerArray('');
    }

    /**
     * @test
     */
    public function getAsTrimmedArrayWithInexistentKeyReturnsEmptyArray(): void
    {
        self::assertSame([], $this->subject->getAsTrimmedArray('foo'));
    }

    /**
     * @test
     */
    public function getAsIntegerArrayWithInexistentKeyReturnsEmptyArray(): void
    {
        self::assertSame([], $this->subject->getAsIntegerArray('foo'));
    }

    /**
     * @test
     */
    public function getAsIntegerArrayWithEmptyDataReturnsEmptyArray(): void
    {
        $key = 'foo';
        $this->subject->setData([$key => '']);

        self::assertSame([], $this->subject->getAsIntegerArray($key));
    }

    /**
     * @test
     */
    public function getAsIntegerArraySplitsCommaSeparatedString(): void
    {
        $key = 'foo';
        $this->subject->setData([$key => '7,4']);

        self::assertSame([7, 4], $this->subject->getAsIntegerArray($key));
    }

    /**
     * @test
     *
     * @dataProvider integerDataProvider
     */
    public function getAsIntegerArrayCastsValuesToInteger(int|string|float|bool $inputValue, int $expected): void
    {
        $key = 'foo';
        $this->subject->setData([$key => $inputValue]);

        self::assertSame([$expected], $this->subject->getAsIntegerArray($key));
    }

    /**
     * @test
     */
    public function getAsTrimmedArrayWithEmptyDataReturnsEmptyArray(): void
    {
        $key = 'foo';
        $this->subject->setData([$key => '']);

        self::assertSame([], $this->subject->getAsTrimmedArray($key));
    }

    /**
     * @test
     */
    public function getAsTrimmedArraySplitsCommaSeparatedString(): void
    {
        $key = 'foo';
        $this->subject->setData([$key => 'hey,ho']);

        self::assertSame(['hey', 'ho'], $this->subject->getAsTrimmedArray($key));
    }

    /**
     * @test
     */
    public function getAsTrimmedArrayTrimsValues(): void
    {
        $key = 'foo';
        $this->subject->setData([$key => ' hey , ho ']);

        self::assertSame(['hey', 'ho'], $this->subject->getAsTrimmedArray($key));
    }

    /**
     * @test
     */
    public function getAsBooleanWithEmptyKeyThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$key must not be empty.');
        $this->expectExceptionCode(1_331_488_963);

        // @phpstan-ignore-next-line We are explicitly checking for a contract violation here.
        $this->subject->getAsBoolean('');
    }

    /**
     * @test
     */
    public function getAsBooleanWithInexistentKeyReturnsFalse(): void
    {
        self::assertFalse($this->subject->getAsBoolean('foo'));
    }

    /**
     * @return array<string, array{0: bool|int|string, 1: bool}>
     */
    public static function booleanDataProvider(): array
    {
        return [
            'boolean false' => [false, false],
            'boolean true' => [true, true],
            'integer 0' => [0, false],
            'integer 1' => [1, true],
            'string 0' => ['0', false],
            'string 1' => ['1', true],
            'empty string 0' => ['', false],
            'some other string' => ['hello', true],
        ];
    }

    /**
     * @test
     *
     * @dataProvider booleanDataProvider
     */
    public function getAsBooleanCastsDataToBoolean(bool|int|string $inputValue, bool $expected): void
    {
        $key = 'foo';
        $this->subject->setData([$key => $inputValue]);

        self::assertSame($expected, $this->subject->getAsBoolean($key));
    }

    /**
     * @test
     */
    public function getAsFloatWithEmptyKeyThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$key must not be empty.');
        $this->expectExceptionCode(1_331_488_963);

        // @phpstan-ignore-next-line We are explicitly checking for a contract violation here.
        $this->subject->getAsFloat('');
    }

    /**
     * @test
     */
    public function getAsFloatWithInexistentKeyReturnsZero(): void
    {
        self::assertSame(0.0, $this->subject->getAsFloat('foo'));
    }

    /**
     * @return array<string, array{0: float|string|int|bool, 1: float}>
     */
    public static function floatDataProvider(): array
    {
        return [
            'zero float' => [0.0, 0.0],
            'positive float' => [12.3, 12.3],
            'negative float' => [-12.3, -12.3],
            'zero float as string' => ['0.0', 0.0],
            'positive float as string' => ['12.3', 12.3],
            'negative float as string' => ['-12.3', -12.3],
            'zero integer' => [0, 0.0],
            'positive integer' => [12, 12.0],
            'negative integer' => [-12, -12.0],
            'zero integer as string' => ['0', 0.0],
            'positive integer as string' => ['12', 12.0],
            'negative integer as string' => ['-12', -12.0],
            'some random string' => ['hello', 0.0],
            'boolean true' => [true, 1.0],
            'boolean false' => [false, 0.0],
        ];
    }

    /**
     * @test
     *
     * @dataProvider floatDataProvider
     */
    public function getAsFloatCastsDataToFloat(float|string|int|bool $inputValue, float $expected): void
    {
        $key = 'foo';
        $this->subject->setData([$key => $inputValue]);

        self::assertEqualsWithDelta($expected, $this->subject->getAsFloat($key), 0.001);
    }

    /**
     * @test
     */
    public function hasStringForNonEmptyStringReturnsTrue(): void
    {
        $key = 'foo';
        $this->subject->setData([$key => 'bar']);

        self::assertTrue($this->subject->hasString($key));
    }

    /**
     * @test
     */
    public function hasStringForEmptyStringReturnsFalse(): void
    {
        $key = 'foo';
        $this->subject->setData([$key => '']);

        self::assertFalse($this->subject->hasString($key));
    }

    /**
     * @test
     */
    public function hasIntegerForPositiveIntegerReturnsTrue(): void
    {
        $key = 'foo';
        $this->subject->setData([$key => 42]);

        self::assertTrue($this->subject->hasInteger($key));
    }

    /**
     * @test
     */
    public function hasIntegerForNegativeIntegerReturnsTrue(): void
    {
        $key = 'foo';
        $this->subject->setData([$key => -42]);

        self::assertTrue($this->subject->hasInteger($key));
    }

    /**
     * @test
     */
    public function hasIntegerForZeroReturnsFalse(): void
    {
        $key = 'foo';
        $this->subject->setData([$key => 0]);

        self::assertFalse($this->subject->hasInteger($key));
    }

    /**
     * @test
     */
    public function hasFloatForPositiveFloatReturnsTrue(): void
    {
        $key = 'foo';
        $this->subject->setData([$key => 42.1]);

        self::assertTrue($this->subject->hasFloat($key));
    }

    /**
     * @test
     */
    public function hasFloatForNegativeFloatReturnsTrue(): void
    {
        $key = 'foo';
        $this->subject->setData([$key => -42.1]);

        self::assertTrue($this->subject->hasFloat($key));
    }

    /**
     * @test
     */
    public function hasFloatForZeroReturnsFalse(): void
    {
        $key = 'foo';
        $this->subject->setData([$key => 0.0]);

        self::assertFalse($this->subject->hasFloat($key));
    }
}
