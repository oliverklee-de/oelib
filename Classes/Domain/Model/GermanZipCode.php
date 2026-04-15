<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Domain\Model;

use OliverKlee\Oelib\Interfaces\Geo;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * This class represents a ZIP code within a city in Germany.
 *
 * The data comes from static tables.
 */
class GermanZipCode extends AbstractEntity implements Geo
{
    protected string $zipCode = '';

    protected string $cityName = '';

    protected float $longitude = 0.0;

    protected float $latitude = 0.0;

    public function getZipCode(): string
    {
        return $this->zipCode;
    }

    public function setZipCode(string $zipCode): void
    {
        $this->zipCode = $zipCode;
    }

    public function getCityName(): string
    {
        return $this->cityName;
    }

    public function setCityName(string $cityName): void
    {
        $this->cityName = $cityName;
    }

    /**
     * Returns this object's address formatted for a geocoding lookup, for example
     * "Pariser Str. 50, 53117 Auerberg, Bonn, DE". Any part of this address
     * might be missing, though.
     *
     * @return string this object's address formatted for a geocoding lookup,
     *                will be empty if this object has no address
     *
     * @deprecated #2205 will be removed in oelib 7.0
     */
    public function getGeoAddress(): string
    {
        return $this->getZipCode() . ' ' . $this->getCityName() . ', DE';
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): void
    {
        $this->longitude = $longitude;
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): void
    {
        $this->latitude = $latitude;
    }

    /**
     * @return true
     *
     * @deprecated #2205 will be removed in oelib 7.0
     */
    public function hasGeoAddress(): bool
    {
        return true;
    }

    /**
     * @return array{latitude: float, longitude: float}
     *
     * @deprecated #2205 will be removed in oelib 7.0
     */
    public function getGeoCoordinates(): array
    {
        return [
            'latitude' => $this->getLatitude(),
            'longitude' => $this->getLongitude(),
        ];
    }

    /**
     * @param array{latitude: float, longitude: float} $coordinates
     *
     * @return never
     *
     * @throws \BadMethodCallException
     *
     * @deprecated #2205 will be removed in oelib 7.0
     */
    public function setGeoCoordinates(array $coordinates): void
    {
        throw new \BadMethodCallException('This method must not be called.', 1_542_211_338);
    }

    /**
     * @deprecated #2205 will be removed in oelib 7.0
     */
    public function hasGeoCoordinates(): bool
    {
        return true;
    }

    /**
     * @return never
     *
     * @throws \BadMethodCallException
     *
     * @deprecated #2205 will be removed in oelib 7.0
     */
    public function clearGeoCoordinates(): void
    {
        throw new \BadMethodCallException('This method must not be called.', 1_542_211_386);
    }

    /**
     * @deprecated #2205 will be removed in oelib 7.0
     */
    public function hasGeoError(): bool
    {
        return false;
    }

    /**
     * @return never
     *
     * @throws \BadMethodCallException
     *
     * @deprecated #2205 will be removed in oelib 7.0
     */
    public function setGeoError(string $reason = ''): void
    {
        throw new \BadMethodCallException('This method must not be called.', 1_542_211_438);
    }

    /**
     * @return never
     *
     * @throws \BadMethodCallException
     *
     * @deprecated #2205 will be removed in oelib 7.0
     */
    public function clearGeoError(): void
    {
        throw new \BadMethodCallException('This method must not be called.', 1_542_211_447);
    }
}
