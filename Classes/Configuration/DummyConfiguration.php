<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Configuration;

use OliverKlee\Oelib\DataStructures\AbstractObjectWithPublicAccessors;
use OliverKlee\Oelib\Interfaces\Configuration;

/**
 * Dummy configuration for usage in tests (in place of any configuration: TypoScript, flexforms, extension manager).
 */
final class DummyConfiguration extends AbstractObjectWithPublicAccessors implements Configuration
{
    /**
     * @var non-empty-string
     */
    private string $sourceName = 'dummy configuration for testing';

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(private array $data = []) {}

    /**
     * Sets all data.
     *
     * @param array<string, mixed> $data
     */
    public function setAllData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * Returns the name of the configuration source, e.g., "TypoScript setup" or "Flexforms".
     *
     * This name may also contain HTML.
     *
     * @return non-empty-string
     */
    public function getSourceName(): string
    {
        return $this->sourceName;
    }

    /**
     * @param non-empty-string $sourceName
     */
    public function setSourceName(string $sourceName): void
    {
        $this->sourceName = $sourceName;
    }

    /**
     * Gets the value of the data item for the given key.
     *
     * @param non-empty-string $key
     *
     * @return mixed the data for the given key, will be null if the key has not been set yet
     */
    protected function get(string $key)
    {
        return $this->data[$key] ?? null;
    }

    /**
     * Sets the value of the data item for the given key.
     *
     * Use `setData` if you want to set all data in one step.
     *
     * @param non-empty-string $key
     * @param mixed $value the data for the given key
     */
    protected function set(string $key, $value): void
    {
        $this->data[$key] = $value;
    }
}
