<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Testing;

use OliverKlee\Oelib\Configuration\ConfigurationProxy;
use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\PageFinder;
use OliverKlee\Oelib\Mapper\MapperRegistry;

/**
 * This class takes care of cleaning up oelib after the testing framework.
 *
 * @internal
 *
 * @deprecated #2216 will be removed in oelib 7.0
 */
class TestingFrameworkCleanup
{
    /**
     * Cleans up oelib after running a test.
     */
    public function cleanUp(): void
    {
        ConfigurationProxy::purgeInstances();
        ConfigurationRegistry::purgeInstance();
        MapperRegistry::purgeInstance();
        PageFinder::purgeInstance();
    }
}
