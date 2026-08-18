<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Model;

use OliverKlee\Oelib\Interfaces\MailRole;
use OliverKlee\Oelib\Model\FrontEndUser;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Oelib\Model\FrontEndUser
 */
final class FrontEndUserTest extends UnitTestCase
{
    private FrontEndUser $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new FrontEndUser();
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['TCA']);

        parent::tearDown();
    }

    #[Test]
    public function implementsMailRole(): void
    {
        self::assertInstanceOf(MailRole::class, $this->subject);
    }

    // Tests concerning the name

    #[Test]
    public function hasNameForEmptyNameLastNameAndFirstNameReturnsFalse(): void
    {
        $this->subject->setData(
            [
                'name' => '',
                'first_name' => '',
                'last_name' => '',
            ],
        );

        self::assertFalse(
            $this->subject->hasName(),
        );
    }

    #[Test]
    public function hasNameForNonEmptyUserReturnsFalse(): void
    {
        $this->subject->setData(
            [
                'username' => 'johndoe',
            ],
        );

        self::assertFalse(
            $this->subject->hasName(),
        );
    }

    #[Test]
    public function hasNameForNonEmptyNameReturnsTrue(): void
    {
        $this->subject->setData(
            [
                'name' => 'John Doe',
                'first_name' => '',
                'last_name' => '',
            ],
        );

        self::assertTrue(
            $this->subject->hasName(),
        );
    }

    #[Test]
    public function hasNameForNonEmptyFirstNameReturnsTrue(): void
    {
        $this->subject->setData(
            [
                'name' => '',
                'first_name' => 'John',
                'last_name' => '',
            ],
        );

        self::assertTrue(
            $this->subject->hasName(),
        );
    }

    #[Test]
    public function hasNameForNonEmptyLastNameReturnsTrue(): void
    {
        $this->subject->setData(
            [
                'name' => '',
                'first_name' => '',
                'last_name' => 'Doe',
            ],
        );

        self::assertTrue(
            $this->subject->hasName(),
        );
    }

    #[Test]
    public function getNameForNonEmptyNameReturnsName(): void
    {
        $this->subject->setData(
            [
                'name' => 'John Doe',
            ],
        );

        self::assertSame(
            'John Doe',
            $this->subject->getName(),
        );
    }

    #[Test]
    public function getNameForNonEmptyNameFirstNameAndLastNameReturnsName(): void
    {
        $this->subject->setData(
            [
                'name' => 'John Doe',
                'first_name' => 'Peter',
                'last_name' => 'Pan',
            ],
        );

        self::assertSame(
            'John Doe',
            $this->subject->getName(),
        );
    }

    #[Test]
    public function getNameForEmptyNameAndNonEmptyFirstAndLastNameReturnsFirstAndLastName(): void
    {
        $this->subject->setData(
            [
                'name' => '',
                'first_name' => 'Peter',
                'last_name' => 'Pan',
            ],
        );

        self::assertSame(
            'Peter Pan',
            $this->subject->getName(),
        );
    }

    #[Test]
    public function getNameForNonEmptyFirstAndLastNameAndNonEmptyUserNameReturnsFirstAndLastName(): void
    {
        $this->subject->setData(
            [
                'first_name' => 'Peter',
                'last_name' => 'Pan',
                'username' => 'johndoe',
            ],
        );

        self::assertSame(
            'Peter Pan',
            $this->subject->getName(),
        );
    }

    #[Test]
    public function getNameForEmptyFirstNameAndNonEmptyLastAndUserNameReturnsLastName(): void
    {
        $this->subject->setData(
            [
                'first_name' => '',
                'last_name' => 'Pan',
                'username' => 'johndoe',
            ],
        );

        self::assertSame(
            'Pan',
            $this->subject->getName(),
        );
    }

    #[Test]
    public function getNameForEmptyLastNameAndNonEmptyFirstAndUserNameReturnsFirstName(): void
    {
        $this->subject->setData(
            [
                'first_name' => 'Peter',
                'last_name' => '',
                'username' => 'johndoe',
            ],
        );

        self::assertSame(
            'Peter',
            $this->subject->getName(),
        );
    }

    #[Test]
    public function getNameForEmptyFirstAndLastNameAndNonEmptyUserNameReturnsEmptyString(): void
    {
        $this->subject->setData(
            [
                'first_name' => '',
                'last_name' => '',
                'username' => 'johndoe',
            ],
        );

        self::assertSame('', $this->subject->getName());
    }

    #[Test]
    public function setNameSetsFullName(): void
    {
        $this->subject->setName('Alfred E. Neumann');

        self::assertSame(
            'Alfred E. Neumann',
            $this->subject->getName(),
        );
    }

    // Tests concerning getting the company

    #[Test]
    public function hasCompanyForEmptyCompanyReturnsFalse(): void
    {
        $this->subject->setData(['company' => '']);

        self::assertFalse(
            $this->subject->hasCompany(),
        );
    }

    #[Test]
    public function hasCompanyForNonEmptyCompanyReturnsTrue(): void
    {
        $this->subject->setData(['company' => 'Test Inc.']);

        self::assertTrue(
            $this->subject->hasCompany(),
        );
    }

    #[Test]
    public function getCompanyForEmptyCompanyReturnsEmptyString(): void
    {
        $this->subject->setData(['company' => '']);

        self::assertSame(
            '',
            $this->subject->getCompany(),
        );
    }

    #[Test]
    public function getCompanyForNonEmptyCompanyReturnsCompany(): void
    {
        $this->subject->setData(['company' => 'Test Inc.']);

        self::assertSame(
            'Test Inc.',
            $this->subject->getCompany(),
        );
    }

    #[Test]
    public function setCompanySetsCompany(): void
    {
        $this->subject->setCompany('Test Inc.');

        self::assertSame(
            'Test Inc.',
            $this->subject->getCompany(),
        );
    }

    // Tests concerning getting the street

    #[Test]
    public function hasStreetForEmptyAddressReturnsFalse(): void
    {
        $this->subject->setData(['address' => '']);

        self::assertFalse(
            $this->subject->hasStreet(),
        );
    }

    #[Test]
    public function hasStreetForNonEmptyAddressReturnsTrue(): void
    {
        $this->subject->setData(['address' => 'Foo street 1']);

        self::assertTrue(
            $this->subject->hasStreet(),
        );
    }

    #[Test]
    public function getStreetForEmptyAddressReturnsEmptyString(): void
    {
        $this->subject->setData(['address' => '']);

        self::assertSame(
            '',
            $this->subject->getStreet(),
        );
    }

    #[Test]
    public function getStreetForNonEmptyAddressReturnsAddress(): void
    {
        $this->subject->setData(['address' => 'Foo street 1']);

        self::assertSame(
            'Foo street 1',
            $this->subject->getStreet(),
        );
    }

    #[Test]
    public function getStreetForMultilineAddressReturnsAddress(): void
    {
        $this->subject->setData(
            [
                'address' => "Foo street 1\nFloor 3",
            ],
        );

        self::assertSame(
            "Foo street 1\nFloor 3",
            $this->subject->getStreet(),
        );
    }

    #[Test]
    public function setStreetSetsStreet(): void
    {
        $street = 'Barber Street 42';
        $this->subject->setData([]);
        $this->subject->setStreet($street);

        self::assertSame(
            $street,
            $this->subject->getStreet(),
        );
    }

    // Tests concerning the ZIP code

    #[Test]
    public function hasZipForEmptyZipReturnsFalse(): void
    {
        $this->subject->setData(['zip' => '']);

        self::assertFalse(
            $this->subject->hasZip(),
        );
    }

    #[Test]
    public function hasZipForNonEmptyZipReturnsTrue(): void
    {
        $this->subject->setData(['zip' => '12345']);

        self::assertTrue(
            $this->subject->hasZip(),
        );
    }

    #[Test]
    public function getZipForEmptyZipReturnsEmptyString(): void
    {
        $this->subject->setData(['zip' => '']);

        self::assertSame(
            '',
            $this->subject->getZip(),
        );
    }

    #[Test]
    public function getZipForNonEmptyZipReturnsZip(): void
    {
        $this->subject->setData(['zip' => '12345']);

        self::assertSame(
            '12345',
            $this->subject->getZip(),
        );
    }

    #[Test]
    public function setZipSetsZip(): void
    {
        $zip = '12356';
        $this->subject->setData([]);
        $this->subject->setZip($zip);

        self::assertSame(
            $zip,
            $this->subject->getZip(),
        );
    }

    // Tests concerning the city

    #[Test]
    public function hasCityForEmptyCityReturnsFalse(): void
    {
        $this->subject->setData(['city' => '']);

        self::assertFalse(
            $this->subject->hasCity(),
        );
    }

    #[Test]
    public function hasCityForNonEmptyCityReturnsTrue(): void
    {
        $this->subject->setData(['city' => 'Test city']);

        self::assertTrue(
            $this->subject->hasCity(),
        );
    }

    #[Test]
    public function getCityForEmptyCityReturnsEmptyString(): void
    {
        $this->subject->setData(['city' => '']);

        self::assertSame(
            '',
            $this->subject->getCity(),
        );
    }

    #[Test]
    public function getCityForNonEmptyCityReturnsCity(): void
    {
        $this->subject->setData(['city' => 'Test city']);

        self::assertSame(
            'Test city',
            $this->subject->getCity(),
        );
    }

    #[Test]
    public function setCitySetsCity(): void
    {
        $city = 'Köln';
        $this->subject->setData([]);
        $this->subject->setCity($city);

        self::assertSame(
            $city,
            $this->subject->getCity(),
        );
    }

    // Tests concerning the phone number

    #[Test]
    public function hasPhoneNumberForEmptyPhoneReturnsFalse(): void
    {
        $this->subject->setData(['telephone' => '']);

        self::assertFalse(
            $this->subject->hasPhoneNumber(),
        );
    }

    #[Test]
    public function hasPhoneNumberForNonEmptyPhoneReturnsTrue(): void
    {
        $this->subject->setData(['telephone' => '1234 5678']);

        self::assertTrue(
            $this->subject->hasPhoneNumber(),
        );
    }

    #[Test]
    public function getPhoneNumberForEmptyPhoneReturnsEmptyString(): void
    {
        $this->subject->setData(['telephone' => '']);

        self::assertSame(
            '',
            $this->subject->getPhoneNumber(),
        );
    }

    #[Test]
    public function getPhoneNumberForNonEmptyPhoneReturnsPhone(): void
    {
        $this->subject->setData(['telephone' => '1234 5678']);

        self::assertSame(
            '1234 5678',
            $this->subject->getPhoneNumber(),
        );
    }

    #[Test]
    public function setPhoneNumberSetsPhoneNumber(): void
    {
        $phoneNumber = '+49 124 1234123';
        $this->subject->setData([]);
        $this->subject->setPhoneNumber($phoneNumber);

        self::assertSame(
            $phoneNumber,
            $this->subject->getPhoneNumber(),
        );
    }

    // Tests concerning the email address

    #[Test]
    public function hasEmailAddressForEmptyEmailReturnsFalse(): void
    {
        $this->subject->setData(['email' => '']);

        self::assertFalse(
            $this->subject->hasEmailAddress(),
        );
    }

    #[Test]
    public function hasEmailAddressForNonEmptyEmailReturnsTrue(): void
    {
        $this->subject->setData(['email' => 'john@doe.com']);

        self::assertTrue(
            $this->subject->hasEmailAddress(),
        );
    }

    #[Test]
    public function getEmailAddressForEmptyEmailReturnsEmptyString(): void
    {
        $this->subject->setData(['email' => '']);

        self::assertSame(
            '',
            $this->subject->getEmailAddress(),
        );
    }

    #[Test]
    public function getEmailAddressForNonEmptyEmailReturnsEmail(): void
    {
        $this->subject->setData(['email' => 'john@doe.com']);

        self::assertSame(
            'john@doe.com',
            $this->subject->getEmailAddress(),
        );
    }

    #[Test]
    public function setEmailAddressSetsEmailAddress(): void
    {
        $this->subject->setEmailAddress('john@example.com');

        self::assertSame(
            'john@example.com',
            $this->subject->getEmailAddress(),
        );
    }

    // Tests concerning the first name

    #[Test]
    public function hasFirstNameForNoFirstNameSetReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasFirstName(),
        );
    }

    #[Test]
    public function hasFirstNameForFirstNameSetReturnsTrue(): void
    {
        $this->subject->setData(['first_name' => 'foo']);

        self::assertTrue(
            $this->subject->hasFirstName(),
        );
    }

    #[Test]
    public function getFirstNameForNoFirstNameSetReturnsEmptyString(): void
    {
        $this->subject->setData([]);

        self::assertSame(
            '',
            $this->subject->getFirstName(),
        );
    }

    #[Test]
    public function getFirstNameForFirstNameSetReturnsFirstName(): void
    {
        $this->subject->setData(['first_name' => 'foo']);

        self::assertSame(
            'foo',
            $this->subject->getFirstName(),
        );
    }

    #[Test]
    public function setFirstNameSetsFirstName(): void
    {
        $this->subject->setFirstName('John');

        self::assertSame(
            'John',
            $this->subject->getFirstName(),
        );
    }

    #[Test]
    public function getFirstOrFullNameForUserWithFirstNameReturnsFirstName(): void
    {
        $this->subject->setData(
            ['first_name' => 'foo', 'name' => 'foo bar'],
        );

        self::assertSame(
            'foo',
            $this->subject->getFirstOrFullName(),
        );
    }

    #[Test]
    public function getFirstOrFullNameForUserWithoutFirstNameReturnsName(): void
    {
        $this->subject->setData(['name' => 'foo bar']);

        self::assertSame(
            'foo bar',
            $this->subject->getFirstOrFullName(),
        );
    }

    // Tests concerning the last name

    #[Test]
    public function hasLastNameForNoLastNameSetReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasLastName(),
        );
    }

    #[Test]
    public function hasLastNameForLastNameSetReturnsTrue(): void
    {
        $this->subject->setData(['last_name' => 'bar']);

        self::assertTrue(
            $this->subject->hasLastName(),
        );
    }

    #[Test]
    public function getLastNameForNoLastNameSetReturnsEmptyString(): void
    {
        $this->subject->setData([]);

        self::assertSame(
            '',
            $this->subject->getLastName(),
        );
    }

    #[Test]
    public function getLastNameForLastNameSetReturnsLastName(): void
    {
        $this->subject->setData(['last_name' => 'bar']);

        self::assertSame(
            'bar',
            $this->subject->getLastName(),
        );
    }

    #[Test]
    public function setLastNameSetsLastName(): void
    {
        $this->subject->setLastName('Jacuzzi');

        self::assertSame(
            'Jacuzzi',
            $this->subject->getLastName(),
        );
    }
}
