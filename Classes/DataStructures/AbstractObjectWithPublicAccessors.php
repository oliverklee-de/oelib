<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\DataStructures;

/**
 * This class represents an object that allows getting and setting its data via public methods.
 */
abstract class AbstractObjectWithPublicAccessors extends AbstractObjectWithAccessors
{
    /**
     * Gets the value stored under the given key, converted to a string.
     *
     * @param non-empty-string $key
     */
    public function getAsString(string $key): string
    {
        return parent::getAsString($key);
    }

    /**
     * Checks whether a non-empty string is stored under the given key.
     *
     * @param non-empty-string $key
     */
    public function hasString(string $key): bool
    {
        return parent::hasString($key);
    }

    /**
     * Sets a value for the given key (and converts it to a string).
     *
     * @param non-empty-string $key
     * @param string|int|float|bool|null $value the value to set, may be empty
     */
    public function setAsString(string $key, $value): void
    {
        parent::setAsString($key, $value);
    }

    /**
     * Gets the value stored under the given key, converted to an integer.
     *
     * @param non-empty-string $key
     */
    public function getAsInteger(string $key): int
    {
        return parent::getAsInteger($key);
    }

    /**
     * Checks whether a non-zero integer is stored under the given key.
     *
     * @param non-empty-string $key
     */
    public function hasInteger(string $key): bool
    {
        return parent::hasInteger($key);
    }

    /**
     * Sets a value for the given key (and converts it to an integer).
     *
     * @param non-empty-string $key
     * @param string|int|float|bool|null $value the value to set, may be empty
     */
    public function setAsInteger(string $key, $value): void
    {
        parent::setAsInteger($key, $value);
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
    public function getAsNonNegativeInteger(string $key): int
    {
        return parent::getAsNonNegativeInteger($key);
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
    public function getAsPositiveInteger(string $key): int
    {
        return parent::getAsPositiveInteger($key);
    }

    /**
     * Gets the value stored under the provided key, converted to an array of trimmed strings.
     *
     * @param non-empty-string $key
     *
     * @return list<non-empty-string> the array value of the given key, may be empty
     */
    public function getAsTrimmedArray(string $key): array
    {
        return parent::getAsTrimmedArray($key);
    }

    /**
     * Gets the value stored under the given key, converted to an array of integers.
     *
     * @param non-empty-string $key
     *
     * @return array<int, int> the array value of the given key, may be empty
     */
    public function getAsIntegerArray(string $key): array
    {
        return parent::getAsIntegerArray($key);
    }

    /**
     * Sets an array value for the given key.
     *
     * Note: This function is intended for data that does not contain any
     * commas. Commas in the array elements cause getAsTrimmedArray and
     * getAsIntegerArray to split that element at the comma. This is a known
     * limitation.
     *
     * @param non-empty-string $key
     * @param array<string|int|bool|float> $value the value to set
     *
     * @see getAsTrimmedArray
     * @see getAsIntegerArray
     */
    public function setAsArray(string $key, array $value): void
    {
        parent::setAsArray($key, $value);
    }

    /**
     * Gets the value stored under the given key, converted to a boolean.
     *
     * @param non-empty-string $key
     */
    public function getAsBoolean(string $key): bool
    {
        return parent::getAsBoolean($key);
    }

    /**
     * Sets a value for the given key (and converts it to a boolean).
     *
     * @param non-empty-string $key
     * @param string|int|float|bool|null $value the value to set, may be empty
     */
    public function setAsBoolean(string $key, $value): void
    {
        parent::setAsBoolean($key, $value);
    }

    /**
     * Gets the value stored under the given key, converted to a float.
     *
     * @param non-empty-string $key
     */
    public function getAsFloat(string $key): float
    {
        return parent::getAsFloat($key);
    }

    /**
     * Checks whether a non-zero float is stored under the given key.
     *
     * @param non-empty-string $key
     */
    public function hasFloat(string $key): bool
    {
        return parent::hasFloat($key);
    }

    /**
     * Sets a value for the given key (and converts it to a float).
     *
     * @param non-empty-string $key
     * @param string|int|float|bool|null $value the value to set, may be empty
     */
    public function setAsFloat(string $key, $value): void
    {
        parent::setAsFloat($key, $value);
    }
}
