<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\DataStructures;

/**
 * This class represents an object that allows getting and setting its data,
 * but only via protected methods so that encapsulation is retained.
 */
abstract class AbstractObjectWithAccessors extends AbstractReadOnlyObjectWithAccessors
{
    /**
     * Sets the value of the data item for the given key.
     *
     * @param non-empty-string $key
     * @param string|int|float|bool|object|null $value the data for the given key
     */
    abstract protected function set(string $key, $value): void;

    /**
     * Sets a value for the given key (and converts it to a string).
     *
     * @param non-empty-string $key
     * @param string|int|float|bool|null $value the value to set, may be empty
     */
    protected function setAsString(string $key, $value): void
    {
        $this->checkForNonEmptyKey($key);

        $this->set($key, (string)$value);
    }

    /**
     * Sets a value for the given key (and converts it to an integer).
     *
     * @param non-empty-string $key
     * @param string|int|float|bool|null $value the value to set, may be empty
     */
    protected function setAsInteger(string $key, $value): void
    {
        $this->checkForNonEmptyKey($key);

        $this->set($key, (int)$value);
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
     * @see getAsIntegerArray
     * @see getAsTrimmedArray
     */
    protected function setAsArray(string $key, array $value): void
    {
        $this->setAsString($key, implode(',', $value));
    }

    /**
     * Sets a value for the given key (and converts it to a boolean).
     *
     * @param non-empty-string $key
     * @param string|int|float|bool|null $value the value to set, may be empty
     */
    protected function setAsBoolean(string $key, $value): void
    {
        $this->checkForNonEmptyKey($key);

        $this->set($key, (int)(bool)$value);
    }

    /**
     * Sets a value for the given key (and converts it to a float).
     *
     * @param non-empty-string $key
     * @param string|int|float|bool|null $value the value to set, may be empty
     */
    protected function setAsFloat(string $key, $value): void
    {
        $this->checkForNonEmptyKey($key);

        $this->set($key, (float)$value);
    }
}
