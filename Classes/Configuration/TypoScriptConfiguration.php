<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Configuration;

use OliverKlee\Oelib\DataStructures\AbstractReadOnlyObjectWithPublicAccessors;
use OliverKlee\Oelib\Interfaces\Configuration as ConfigurationInterface;

/**
 * This class represents a set of configuration values within a certain namespace.
 */
class TypoScriptConfiguration extends AbstractReadOnlyObjectWithPublicAccessors implements ConfigurationInterface
{
    /**
     * @var array<string|int, mixed> the data for this configuration
     */
    private array $data = [];

    /**
     * Returns the name of the configuration source, e.g., "TypoScript setup" or "Flexforms".
     *
     * This name may also contain HTML.
     *
     * @return non-empty-string
     */
    public function getSourceName(): string
    {
        return 'in your TypoScript template';
    }

    /**
     * Sets the complete data for this configuration.
     *
     * This function can be called multiple times.
     *
     * @param array<string|int, mixed> $data the data for this configuration, may be empty
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * Gets the value of the data item for the given key.
     *
     * @param non-empty-string $key
     */
    protected function get(string $key): mixed
    {
        if (!$this->existsKey($key)) {
            return '';
        }

        return $this->data[$key];
    }

    /**
     * Checks whether a data item with a certain key exists.
     *
     * @param non-empty-string $key
     *
     * @return bool whether a data item with the key $key exists
     */
    protected function existsKey(string $key): bool
    {
        return isset($this->data[$key]);
    }

    /**
     * Returns the array keys of the data item for the given key.
     *
     * If $key is an empty string the array keys of $this->data are returned.
     *
     * @param string $key the key of the data item to get the array keys for, may be empty
     *
     * @return list<string|int> the array keys of the data item for the given key, may be empty
     */
    public function getArrayKeys(string $key = ''): array
    {
        if ($key === '') {
            return array_keys($this->data);
        }

        if (!$this->existsKey($key) || !is_array($this->data[$key])) {
            return [];
        }

        return array_keys($this->data[$key]);
    }

    /**
     * Returns the data for the given key as a multidimensional array.
     *
     * The return value will be an empty array:
     * - if the data item is an empty array,
     * - if the data item is not an array,
     * - if the key does not exist in $this->data.
     *
     * @param non-empty-string $key the key of the data item to get as a multidimensional array
     *
     * @return array<string, array<string|int, mixed>|string|int> the data for the given key, may be empty
     */
    public function getAsMultidimensionalArray(string $key): array
    {
        if (!isset($this->data[$key]) || !is_array($this->data[$key])) {
            return [];
        }

        return $this->data[$key];
    }
}
