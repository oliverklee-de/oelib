<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Email;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MailUtility;

/**
 * This class builds email subjects with the email data from the install tool.
 *
 * @deprecated Will be removed in oelib 7.0 in #2293. Use `SystemEmailBuilder` instead.
 */
class SystemEmailFromBuilder
{
    /**
     * Checks whether a valid email address has been set as defaultMailFromAddress.
     */
    public function canBuild(): bool
    {
        $configuration = $this->getEmailConfiguration();
        $emailAddress = $configuration['defaultMailFromAddress'] ?? '';

        return \filter_var($emailAddress, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * @return array{defaultMailFromAddress?: string}
     */
    protected function getEmailConfiguration(): array
    {
        return $GLOBALS['TYPO3_CONF_VARS']['MAIL'];
    }

    /**
     * @throws \UnexpectedValueException
     */
    public function build(): GeneralEmailRole
    {
        if (!$this->canBuild()) {
            throw new \UnexpectedValueException(
                'Please set a TYPO3_CONF_VARS/MAIL/defaultMailFromAddress configuration first.',
                1_542_793_620,
            );
        }

        return GeneralUtility::makeInstance(
            GeneralEmailRole::class,
            MailUtility::getSystemFromAddress(),
            (string)MailUtility::getSystemFromName(),
        );
    }
}
