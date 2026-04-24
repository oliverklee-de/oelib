<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Mapper;

use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Tests\Unit\Mapper\Fixtures\TestingChildMapper;
use OliverKlee\Oelib\Tests\Unit\Mapper\Fixtures\TestingMapper;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Oelib\Mapper\MapperRegistry
 */
final class MapperRegistryTest extends UnitTestCase
{
    private MapperRegistry $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new MapperRegistry();
    }

    protected function tearDown(): void
    {
        MapperRegistry::purgeInstance();

        parent::tearDown();
    }

    /**
     * @test
     */
    public function getInstanceReturnsMapperRegistryInstance(): void
    {
        self::assertInstanceOf(
            MapperRegistry::class,
            MapperRegistry::getInstance(),
        );
    }

    /**
     * @test
     */
    public function getInstanceTwoTimesReturnsSameInstance(): void
    {
        self::assertSame(
            MapperRegistry::getInstance(),
            MapperRegistry::getInstance(),
        );
    }

    /**
     * @test
     */
    public function getInstanceAfterPurgeInstanceReturnsNewInstance(): void
    {
        $firstInstance = MapperRegistry::getInstance();
        MapperRegistry::purgeInstance();

        self::assertNotSame(
            $firstInstance,
            MapperRegistry::getInstance(),
        );
    }

    /**
     * @test
     */
    public function getForEmptyKeyThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$className must not be empty.');
        $this->expectExceptionCode(1331488868);

        // @phpstan-ignore-next-line We explicitly check for contract violations here.
        MapperRegistry::get('');
    }

    /**
     * @test
     */
    public function getForInexistentClassThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No mapper class');
        $this->expectExceptionCode(1632844178);

        // @phpstan-ignore-next-line We're testing a contract violation here on purpose.
        MapperRegistry::get('InexistentMapper');
    }

    /**
     * @test
     */
    public function getForExistingClassReturnsObjectOfRequestedClass(): void
    {
        self::assertInstanceOf(TestingMapper::class, MapperRegistry::get(TestingMapper::class));
    }

    /**
     * @test
     */
    public function getForExistingClassCalledTwoTimesReturnsTheSameInstance(): void
    {
        self::assertSame(
            MapperRegistry::get(TestingMapper::class),
            MapperRegistry::get(TestingMapper::class),
        );
    }

    /**
     * @test
     */
    public function getReturnsMapperSetViaSet(): void
    {
        $mapper = new TestingMapper();
        MapperRegistry::set(TestingMapper::class, $mapper);

        self::assertSame(
            $mapper,
            MapperRegistry::get(TestingMapper::class),
        );
    }

    /**
     * @test
     */
    public function getByClassNameForEmptyKeyThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$className must not be empty.');
        $this->expectExceptionCode(1331488868);

        // @phpstan-ignore-next-line We explicitly check for contract violations here.
        $this->subject->getByClassName('');
    }

    /**
     * @test
     */
    public function getByClassNameForInexistentClassThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No mapper class');
        $this->expectExceptionCode(1632844178);

        // @phpstan-ignore-next-line We're testing a contract violation here on purpose.
        $this->subject->getByClassName('InexistentMapper');
    }

    /**
     * @test
     */
    public function getByClassNameForExistingClassReturnsObjectOfRequestedClass(): void
    {
        self::assertInstanceOf(TestingMapper::class, $this->subject->getByClassName(TestingMapper::class));
    }

    /**
     * @test
     */
    public function getByClassNameForExistingClassCalledTwoTimesReturnsTheSameInstance(): void
    {
        self::assertSame(
            $this->subject->getByClassName(TestingMapper::class),
            $this->subject->getByClassName(TestingMapper::class),
        );
    }

    /**
     * @test
     */
    public function getByClassNameReturnsMapperSetViaSetByClassName(): void
    {
        $mapper = new TestingMapper();
        $this->subject->setByClassName(TestingMapper::class, $mapper);

        self::assertSame(
            $mapper,
            $this->subject->getByClassName(TestingMapper::class),
        );
    }

    /**
     * @test
     */
    public function setThrowsExceptionForMismatchingWrapperClass(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The provided mapper is not an instance of');
        $this->expectExceptionCode(1331488915);

        $mapper = new TestingMapper();
        MapperRegistry::set(TestingChildMapper::class, $mapper);
    }

    /**
     * @test
     */
    public function setThrowsExceptionIfTheMapperTypeAlreadyIsRegistered(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Overwriting existing mappers is not allowed.');
        $this->expectExceptionCode(1331488928);

        MapperRegistry::get(TestingMapper::class);

        $mapper = new TestingMapper();
        MapperRegistry::set(TestingMapper::class, $mapper);
    }

    /**
     * @test
     */
    public function setByClassNameThrowsExceptionForMismatchingWrapperClass(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The provided mapper is not an instance of');
        $this->expectExceptionCode(1331488915);

        $mapper = new TestingMapper();
        $this->subject->setByClassName(TestingChildMapper::class, $mapper);
    }

    /**
     * @test
     */
    public function setByClassNameThrowsExceptionIfTheMapperTypeAlreadyIsRegistered(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Overwriting existing mappers is not allowed.');
        $this->expectExceptionCode(1331488928);

        $this->subject->getByClassName(TestingMapper::class);

        $mapper = new TestingMapper();
        $this->subject->setByClassName(TestingMapper::class, $mapper);
    }
}
