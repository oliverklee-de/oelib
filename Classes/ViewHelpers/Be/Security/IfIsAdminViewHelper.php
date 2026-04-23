<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\ViewHelpers\Be\Security;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

/**
 * This ViewHelper checks whether a BE admin is logged in.
 *
 * Example
 * =======
 *
 * Basic usage
 * -----------
 *
 * ::
 *     {namespace oelib=OliverKlee\Oelib\ViewHelpers}
 *     <oelib:be.security.ifIsAdmin>
 *         Ooh, you are an admin …
 *     </oelib:be.security.ifIsAdmin>
 *
 * You can also use if/then/else constructs like with the `f:if` ViewHelper.
 *
 * If / then / else
 * ----------------
 *
 * ::
 *
 *     <oelib:be.security.ifIsAdmin>
 *         <f:then>
 *             Welcome!
 *         </f:then>
 *         <f:else>
 *             You shall not pass!
 *         </f:else>
 *     </oelib:be.security.ifIsAdmin>
 *
 * @api
 */
class IfIsAdminViewHelper extends AbstractConditionViewHelper
{
    /**
     * @param array<string, mixed> $arguments
     */
    public static function verdict(array $arguments, RenderingContextInterface $renderingContext): bool
    {
        // Note: We cannot use DI for obtaining the context as `verdict` is static.
        $context = GeneralUtility::makeInstance(Context::class);

        return $context->getAspect('backend.user')->isAdmin();
    }
}
