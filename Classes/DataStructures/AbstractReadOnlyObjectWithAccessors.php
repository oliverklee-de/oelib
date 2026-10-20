<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\DataStructures;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class represents an object that allows getting its data,
 * but only via protected methods so that encapsulation is retained.
 */
abstract class AbstractReadOnlyObjectWithAccessors
{
    /**
     * Gets the value of the data item for the given key.
     *
     * @param non-empty-string $key
     */
    abstract protected function get(string $key): mixed;

    /**
     * Checks that `$key` is not empty.
     *
     * @param string $key the key to check
     *
     * @throws \InvalidArgumentException if $key is empty
     */
    protected function checkForNonEmptyKey(string $key): void
    {
        if ($key === '') {
            throw new \InvalidArgumentException('$key must not be empty.', 1_331_488_963);
        }
    }

    /**
     * Gets the value stored under the given key, converted to a string.
     *
     * @param non-empty-string $key
     */
    protected function getAsString(string $key): string
    {
        $this->checkForNonEmptyKey($key);

        return trim((string)$this->get($key));
    }

    /**
     * Checks whether a non-empty string is stored under the given key.
     *
     * @param non-empty-string $key
     */
    protected function hasString(string $key): bool
    {
        return $this->getAsString($key) !== '';
    }

    /**
     * Gets the value stored under the given key, converted to an integer.
     *
     * @param non-empty-string $key
     */
    protected function getAsInteger(string $key): int
    {
        $this->checkForNonEmptyKey($key);

        return (int)$this->get($key);
    }

    /**
     * Checks whether a non-zero integer is stored under the given key.
     *
     * @param non-empty-string $key
     */
    protected function hasInteger(string $key): bool
    {
        return $this->getAsInteger($key) !== 0;
    }

    /**
     * Gets the value stored under the given key, converted to an integer.
     *
     * @param non-empty-string $key
     *
     * @return int<0, max>
     *
     * @throws \UnexpectedValueException if the value is negative
     */
    protected function getAsNonNegativeInteger(string $key): int
    {
        $value = $this->getAsInteger($key);

        if ($value < 0) {
            throw new \UnexpectedValueException(
                'The value for the key "' . $key . '" must be a non-negative integer, but it is ' . $value . '.',
                1735299608,
            );
        }

        return $value;
    }

    /**
     * Gets the value stored under the given key, converted to an integer.
     *
     * @param non-empty-string $key
     *
     * @return positive-int
     *
     * @throws \UnexpectedValueException if the value is zero or negative
     */
    protected function getAsPositiveInteger(string $key): int
    {
        $value = $this->getAsInteger($key);

        if ($value <= 0) {
            throw new \UnexpectedValueException(
                'The value for the key "' . $key . '" must be a positive integer, but it is ' . $value . '.',
                1735299700,
            );
        }

        return $value;
    }

    /**
     * Gets the value stored under the provided key, converted to an array of trimmed strings.
     *
     * @param non-empty-string $key
     *
     * @return list<non-empty-string> the array value of the given key, may be empty
     */
    protected function getAsTrimmedArray(string $key): array
    {
        return GeneralUtility::trimExplode(',', $this->getAsString($key), true);
    }

    /**
     * Gets the value stored under the given key, converted to an array of integers.
     *
     * @param non-empty-string $key
     *
     * @return array<int, int> the array value of the given key, may be empty
     */
    protected function getAsIntegerArray(string $key): array
    {
        $stringValue = $this->getAsString($key);

        if ($stringValue === '') {
            return [];
        }

        $result = GeneralUtility::intExplode(',', $stringValue, true);

        return $result;
    }

    /**
     * Gets the value stored under the given key, converted to a boolean.
     *
     * @param non-empty-string $key
     */
    protected function getAsBoolean(string $key): bool
    {
        $this->checkForNonEmptyKey($key);

        return (bool)$this->get($key);
    }

    /**
     * Gets the value stored under the given key, converted to a float.
     *
     * @param non-empty-string $key
     */
    protected function getAsFloat(string $key): float
    {
        $this->checkForNonEmptyKey($key);

        return (float)$this->get($key);
    }

    /**
     * Checks whether a non-zero float is stored under the given key.
     *
     * @param non-empty-string $key
     */
    protected function hasFloat(string $key): bool
    {
        return $this->getAsFloat($key) !== 0.00;
    }
}
