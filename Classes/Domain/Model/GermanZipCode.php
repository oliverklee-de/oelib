<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * This class represents a ZIP code within a city in Germany.
 *
 * The data comes from static tables.
 */
class GermanZipCode extends AbstractEntity
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
}
