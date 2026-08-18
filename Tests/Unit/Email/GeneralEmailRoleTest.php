<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Email;

use OliverKlee\Oelib\Email\GeneralEmailRole;
use OliverKlee\Oelib\Interfaces\MailRole;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Oelib\Email\GeneralEmailRole
 */
final class GeneralEmailRoleTest extends UnitTestCase
{
    #[Test]
    public function implementsMailRole(): void
    {
        $subject = new GeneralEmailRole('jade@example.com');

        self::assertInstanceOf(MailRole::class, $subject);
    }

    #[Test]
    public function usesEmailAddressFromConstructor(): void
    {
        $emailAddress = 'jade@example.com';
        $subject = new GeneralEmailRole($emailAddress);

        self::assertSame($emailAddress, $subject->getEmailAddress());
    }

    #[Test]
    public function usesNameFromConstructor(): void
    {
        $name = 'Jade Jennings';
        $subject = new GeneralEmailRole('jade@example.com', $name);

        self::assertSame($name, $subject->getName());
    }

    #[Test]
    public function hasEmptyNameByDefault(): void
    {
        $subject = new GeneralEmailRole('jade@example.com');

        self::assertSame('', $subject->getName());
    }
}
