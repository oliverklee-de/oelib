<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Model;

use OliverKlee\Oelib\Interfaces\Address;
use OliverKlee\Oelib\Interfaces\MailRole;

/**
 * This class represents a front-end user.
 */
class FrontEndUser extends AbstractModel implements MailRole, Address
{
    /**
     * Gets this user's real name.
     *
     * First, the "name" field is checked. If that is empty, the fields
     * "first_name" and "last_name" are checked. If those are empty as well,
     * the username is returned as a fallback value.
     *
     * @return string the user's real name, will not be empty for valid records
     */
    public function getName(): string
    {
        if ($this->hasString('name')) {
            $result = $this->getAsString('name');
        } elseif ($this->hasFirstName() || $this->hasLastName()) {
            $result = trim($this->getFirstName() . ' ' . $this->getLastName());
        } else {
            $result = '';
        }

        return $result;
    }

    /**
     * Checks whether this user has a non-empty name.
     *
     * @return bool TRUE if this user has a non-empty name, FALSE otherwise
     */
    public function hasName(): bool
    {
        return $this->hasString('name') || $this->hasFirstName()
            || $this->hasLastName();
    }

    /**
     * Sets the full name.
     *
     * @param string $name the name to set, may be empty
     */
    public function setName(string $name): void
    {
        $this->setAsString('name', $name);
    }

    /**
     * Gets this user's company.
     *
     * @return string this user's company, may be empty
     */
    public function getCompany(): string
    {
        return $this->getAsString('company');
    }

    /**
     * Checks whether this user has a non-empty company set.
     *
     * @return bool TRUE if this user has a company set, FALSE otherwise
     */
    public function hasCompany(): bool
    {
        return $this->hasString('company');
    }

    /**
     * Sets the company.
     *
     * @param string $company the company set, may be empty
     */
    public function setCompany(string $company): void
    {
        $this->setAsString('company', $company);
    }

    /**
     * Gets this user's street.
     *
     * @return string this user's street, may be multi-line, may be empty
     */
    public function getStreet(): string
    {
        return $this->getAsString('address');
    }

    /**
     * Checks whether this user has a non-empty street set.
     *
     * @return bool TRUE if this user has a street set, FALSE otherwise
     */
    public function hasStreet(): bool
    {
        return $this->hasString('address');
    }

    /**
     * Sets the street address.
     *
     * @param string $street the street address, may be empty
     */
    public function setStreet(string $street): void
    {
        $this->setAsString('address', $street);
    }

    /**
     * Gets this user's ZIP code.
     *
     * @return string this user's ZIP code, may be empty
     */
    public function getZip(): string
    {
        return $this->getAsString('zip');
    }

    /**
     * Checks whether this user has a non-empty ZIP code set.
     *
     * @return bool TRUE if this user has a ZIP code set, FALSE otherwise
     */
    public function hasZip(): bool
    {
        return $this->hasString('zip');
    }

    /**
     * Sets the ZIP code.
     *
     * @param string $zipCode the ZIP code, may be empty
     */
    public function setZip(string $zipCode): void
    {
        $this->setAsString('zip', $zipCode);
    }

    /**
     * Gets this user's city.
     *
     * @return string this user's city, may be empty
     */
    public function getCity(): string
    {
        return $this->getAsString('city');
    }

    /**
     * Checks whether this user has a non-empty city set.
     *
     * @return bool TRUE if this user has a city set, FALSE otherwise
     */
    public function hasCity(): bool
    {
        return $this->hasString('city');
    }

    /**
     * Sets the city.
     *
     * @param string $city the city name, may be empty
     */
    public function setCity(string $city): void
    {
        $this->setAsString('city', $city);
    }

    /**
     * Gets this user's phone number.
     *
     * @return string this user's phone number, may be empty
     */
    public function getPhoneNumber(): string
    {
        return $this->getAsString('telephone');
    }

    /**
     * Checks whether this user has a non-empty phone number set.
     *
     * @return bool TRUE if this user has a phone number set, FALSE otherwise
     */
    public function hasPhoneNumber(): bool
    {
        return $this->hasString('telephone');
    }

    /**
     * Sets the phone number.
     *
     * @param string $phoneNumber the phone number, may be empty
     */
    public function setPhoneNumber(string $phoneNumber): void
    {
        $this->setAsString('telephone', $phoneNumber);
    }

    /**
     * Gets this user's email address.
     *
     * @return string this user's email address, may be empty
     */
    public function getEmailAddress(): string
    {
        return $this->getAsString('email');
    }

    /**
     * Checks whether this user has a non-empty email address set.
     *
     * @return bool TRUE if this user has an email address set, FALSE
     *                 otherwise
     */
    public function hasEmailAddress(): bool
    {
        return $this->hasString('email');
    }

    /**
     * Sets the email address.
     *
     * @param string $emailAddress the email address to set, may be empty
     */
    public function setEmailAddress(string $emailAddress): void
    {
        $this->setAsString('email', $emailAddress);
    }

    /**
     * Checks whether this user has a first name.
     *
     * @return bool TRUE if the user has a first name, FALSE otherwise
     */
    public function hasFirstName(): bool
    {
        return $this->hasString('first_name');
    }

    /**
     * Gets this user's first name
     *
     * @return string the first name of this user, will be empty if no first
     *                name is set
     */
    public function getFirstName(): string
    {
        return $this->getAsString('first_name');
    }

    /**
     * Sets the first name.
     *
     * @param string $firstName the first name to set, may be empty
     */
    public function setFirstName(string $firstName): void
    {
        $this->setAsString('first_name', $firstName);
    }

    /**
     * Checks whether this user has a last name.
     *
     * @return bool TRUE if the user has a last name, FALSE otherwise
     */
    public function hasLastName(): bool
    {
        return $this->hasString('last_name');
    }

    /**
     * Gets this user's last name
     *
     * @return string the last name of this user, will be empty if no last name
     *                is set
     */
    public function getLastName(): string
    {
        return $this->getAsString('last_name');
    }

    /**
     * Sets the last name.
     *
     * @param string $lastName the last name to set, may be empty
     */
    public function setLastName(string $lastName): void
    {
        $this->setAsString('last_name', $lastName);
    }

    /**
     * Gets this user's first name; if the user does not have a first name the
     * full name is returned instead.
     *
     * @return string the first name of this user if it exists, will return the
     *                user's full name otherwise
     */
    public function getFirstOrFullName(): string
    {
        return $this->hasFirstName() ? $this->getFirstName() : $this->getName();
    }
}
