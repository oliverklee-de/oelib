<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Mapper;

use OliverKlee\Oelib\Mapper\MapperRegistry;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Oelib\Mapper\MapperRegistry
 */
final class MapperRegistryTest extends FunctionalTestCase
{
    protected bool $initializeDatabase = false;

    protected array $testExtensionsToLoad = ['oliverklee/oelib'];

    private MapperRegistry $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = $this->get(MapperRegistry::class);
    }

    /**
     * @test
     */
    public function isAvailableViaContainer(): void
    {
        self::assertInstanceOf(MapperRegistry::class, $this->subject);
    }
}
