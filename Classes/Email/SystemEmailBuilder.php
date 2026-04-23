<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Email;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MailUtility;

/**
 * Creates a `GeneralEmailRole` with the default system email from name and email address.
 *
 * Use via DI.
 */
class SystemEmailBuilder
{
    public function build(): GeneralEmailRole
    {
        $email = MailUtility::getSystemFromAddress();
        $name = MailUtility::getSystemFromName();
        if (!\is_string($name)) {
            $name = '';
        }

        return GeneralUtility::makeInstance(GeneralEmailRole::class, $email, $name);
    }
}
