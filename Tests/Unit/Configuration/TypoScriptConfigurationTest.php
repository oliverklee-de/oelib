<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Configuration;

use OliverKlee\Oelib\Configuration\TypoScriptConfiguration;
use OliverKlee\Oelib\DataStructures\AbstractReadOnlyObjectWithPublicAccessors;
use OliverKlee\Oelib\Interfaces\Configuration as ConfigurationInterface;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Oelib\Configuration\TypoScriptConfiguration
 */
final class TypoScriptConfigurationTest extends UnitTestCase
{
    private TypoScriptConfiguration $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new TypoScriptConfiguration();
    }

    #[Test]
    public function implementsConfigurationInterface(): void
    {
        self::assertInstanceOf(ConfigurationInterface::class, $this->subject);
    }

    #[Test]
    public function hasTypoScriptName(): void
    {
        $subject = new TypoScriptConfiguration();

        self::assertSame('in your TypoScript template', $subject->getSourceName());
    }

    #[Test]
    public function isObjectWithPublicAccessors(): void
    {
        self::assertInstanceOf(AbstractReadOnlyObjectWithPublicAccessors::class, $this->subject);
    }

    // Tests for the basic functionality

    #[Test]
    public function setDataWithEmptyArrayIsAllowed(): void
    {
        $this->subject->setData([]);
    }

    #[Test]
    public function getAfterSetDataReturnsTheSetValue(): void
    {
        $this->subject->setData(
            ['foo' => 'bar'],
        );

        self::assertSame(
            'bar',
            $this->subject->getAsString('foo'),
        );
    }

    #[Test]
    public function setDataCalledTwoTimesDoesNotFail(): void
    {
        $this->subject->setData(
            ['title' => 'bar'],
        );
        $this->subject->setData(
            ['title' => 'bar'],
        );
    }

    ////////////////////////////////////
    // Tests regarding getArrayKeys().
    ////////////////////////////////////
    #[Test]
    public function getArrayKeysWithEmptyKeyReturnsKeysOfDataArray(): void
    {
        $this->subject->setData(['first' => 'test', 'second' => 'test']);

        self::assertSame(
            ['first', 'second'],
            $this->subject->getArrayKeys(),
        );
    }

    #[Test]
    public function getArrayKeysForInexistentKeyReturnEmptyArray(): void
    {
        self::assertSame(
            [],
            $this->subject->getArrayKeys('key'),
        );
    }

    #[Test]
    public function getArrayKeysForKeyOfStringDataItemReturnsEmptyArray(): void
    {
        $this->subject->setData(['key' => 'blub']);

        self::assertSame(
            [],
            $this->subject->getArrayKeys('key'),
        );
    }

    #[Test]
    public function getArrayKeysForKeyOfDataItemWithOneArrayElementReturnsKeyOfArrayElement(): void
    {
        $this->subject->setData(['key' => ['test' => 'child']]);

        self::assertSame(
            ['test'],
            $this->subject->getArrayKeys('key'),
        );
    }

    #[Test]
    public function getArrayKeysForKeyOfDataItemWithTwoArrayElementsReturnsKeysOfArrayElements(): void
    {
        $this->subject->setData(
            ['key' => ['first' => 'child', 'second' => 'child']],
        );

        self::assertSame(
            ['first', 'second'],
            $this->subject->getArrayKeys('key'),
        );
    }

    #[Test]
    public function getAsMultidimensionalArrayReturnsMultidimensionalArray(): void
    {
        $this->subject->setData(
            ['1' => ['1.1' => ['1.1.1' => 'child']]],
        );

        self::assertSame(
            ['1.1' => ['1.1.1' => 'child']],
            $this->subject->getAsMultidimensionalArray('1'),
        );
    }

    #[Test]
    public function getAsMultidimensionalArrayForInexistentKeyReturnsEmptyArray(): void
    {
        $this->subject->setData([]);

        self::assertSame(
            [],
            $this->subject->getAsMultidimensionalArray('1'),
        );
    }

    #[Test]
    public function getAsMultidimensionalArrayForStringReturnsEmptyArray(): void
    {
        $this->subject->setData(
            ['1' => 'child'],
        );

        self::assertSame(
            [],
            $this->subject->getAsMultidimensionalArray('1'),
        );
    }

    #[Test]
    public function getAsMultidimensionalArrayForIntegerReturnsEmptyArray(): void
    {
        $this->subject->setData(
            ['1' => 42],
        );

        self::assertSame(
            [],
            $this->subject->getAsMultidimensionalArray('1'),
        );
    }

    #[Test]
    public function getAsMultidimensionalArrayForFloatReturnsEmptyArray(): void
    {
        $this->subject->setData(
            ['1' => 42.42],
        );

        self::assertSame(
            [],
            $this->subject->getAsMultidimensionalArray('1'),
        );
    }
}
