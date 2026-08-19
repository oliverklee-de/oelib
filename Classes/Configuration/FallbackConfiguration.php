<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Configuration;

use OliverKlee\Oelib\Interfaces\Configuration as ConfigurationInterface;

/**
 * This configuration provides fallback functionality to a secondary configuration
 * if the requested data in the primary configuration is empty.
 */
class FallbackConfiguration implements ConfigurationInterface
{
    public function __construct(
        private readonly ConfigurationInterface $primary,
        private readonly ConfigurationInterface $secondary,
    ) {}

    /**
     * Returns the name of the configuration source, e.g., "TypoScript setup" or "Flexforms".
     *
     * This name may also contain HTML.
     *
     * @return non-empty-string
     */
    public function getSourceName(): string
    {
        return $this->primary->getSourceName() . ' or ' . $this->secondary->getSourceName();
    }

    /**
     * Gets the value stored under the given key, converted to a string.
     *
     * @param non-empty-string $key
     */
    public function getAsString(string $key): string
    {
        return $this->primary->hasString($key)
            ? $this->primary->getAsString($key) : $this->secondary->getAsString($key);
    }

    /**
     * Checks whether a non-empty string is stored under the given key.
     *
     * @param non-empty-string $key
     */
    public function hasString(string $key): bool
    {
        return $this->primary->hasString($key) || $this->secondary->hasString($key);
    }

    /**
     * Gets the value stored under the given key, converted to an integer.
     *
     * @param non-empty-string $key
     */
    public function getAsInteger(string $key): int
    {
        return $this->primary->hasInteger($key)
            ? $this->primary->getAsInteger($key) : $this->secondary->getAsInteger($key);
    }

    /**
     * Checks whether a non-zero integer is stored under the given key.
     *
     * @param non-empty-string $key
     */
    public function hasInteger(string $key): bool
    {
        return $this->primary->hasInteger($key) || $this->secondary->hasInteger($key);
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
        $value = $this->getAsInteger($key);

        if ($value < 0) {
            throw new \UnexpectedValueException(
                'The value for "' . $key . '" must be a non-negative integer, but it is ' . $value . '.',
                1573030133,
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
    public function getAsPositiveInteger(string $key): int
    {
        $value = $this->getAsInteger($key);

        if ($value <= 0) {
            throw new \UnexpectedValueException(
                'The value for "' . $key . '" must be a positive integer, but it is ' . $value . '.',
                1573030133,
            );
        }

        return $value;
    }

    /**
     * Gets the value stored under the given key, converted to a boolean.
     *
     * @param non-empty-string $key
     */
    public function getAsBoolean(string $key): bool
    {
        return $this->primary->getAsBoolean($key) || $this->secondary->getAsBoolean($key);
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
        $primaryValue = $this->primary->getAsTrimmedArray($key);

        return $primaryValue !== [] ? $primaryValue : $this->secondary->getAsTrimmedArray($key);
    }
}
