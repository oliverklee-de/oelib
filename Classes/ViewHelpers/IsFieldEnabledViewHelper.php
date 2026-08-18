<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

/**
 * This ViewHelper implements a condition based on whether the provided field (or fields) are enabled in the
 * settings.
 *
 * You can provide either a single field name or multiple field names separated by the pipe character (which serves
 * as a logical OR).
 *
 * The name of the fields in the settings carrying the list of enabled fields can be changed with the
 * `configurationKey` argument.
 *
 * Examples
 * ========
 *
 * Basic usage
 * -----------
 *
 * ::
 *     {namespace oelib=OliverKlee\Oelib\ViewHelpers}
 *     <oelib:isFieldEnabled fieldName="name">
 *         Here the "name" field should be displayed.
 *     </oelib:isFieldEnabled>
 *
 * Output::
 *
 *     Everything inside the :xml:`<oelib:isFieldEnabled>` tag is being displayed if the field is enabled in the
 * configuration.
 *
 * You can also use if/then/else constructs like with the `f:if` ViewHelper.
 *
 *
 * If / then / else
 * ----------------
 *
 * ::
 *
 *     <oelib:isFieldEnabled fieldName="company|name">
 *         <f:then>
 *             This is being shown in case the condition matches.
 *         </f:then>
 *         <f:else>
 *             This is being displayed in case the condition evaluates to FALSE.
 *         </f:else>
 *     </oelib:isFieldEnabled>
 *
 * @api
 */
class IsFieldEnabledViewHelper extends AbstractConditionViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('fieldName', 'string', 'The name(s) of the fields to check, separated by |.', true);
        $this->registerArgument('configurationKey', 'string', 'The configuration key to use.', false, 'fieldsToShow');
    }

    /**
     * @param array<string, mixed> $arguments
     */
    public static function verdict(array $arguments, RenderingContextInterface $renderingContext): bool
    {
        $configurationKey = $arguments['configurationKey'] ?? null;
        if (!\is_string($configurationKey)) {
            throw new \UnexpectedValueException(
                'The variable "configurationKey" must be a string, but was: ' . \gettype($configurationKey),
                1743708980,
            );
        }
        if ($configurationKey === '') {
            throw new \UnexpectedValueException('The variable "configurationKey" must not be empty.', 1743709004);
        }

        $enabledFields = self::getEnabledFields($renderingContext, $configurationKey);

        $verdict = false;
        foreach (self::getFieldsToCheck($arguments) as $fieldName) {
            if (\in_array($fieldName, $enabledFields, true)) {
                $verdict = true;
                break;
            }
        }

        return $verdict;
    }

    /**
     * @param array<string, mixed> $arguments
     *
     * @return list<non-empty-string>
     *
     * @throws \InvalidArgumentException
     */
    private static function getFieldsToCheck(array $arguments): array
    {
        $fieldsNamesArgument = $arguments['fieldName'] ?? '';
        if (!\is_string($fieldsNamesArgument)) {
            throw new \InvalidArgumentException(
                'The argument "fieldName" must be a string, but was ' . \gettype($fieldsNamesArgument),
                1_651_496_544,
            );
        }

        if ($fieldsNamesArgument === '') {
            throw new \InvalidArgumentException('The argument "fieldName" must not be empty.', 1_651_155_957);
        }

        return GeneralUtility::trimExplode('|', $fieldsNamesArgument, true);
    }

    /**
     * @param non-empty-string $configurationKey
     *
     * @return list<non-empty-string>
     *
     * @throws \UnexpectedValueException
     */
    private static function getEnabledFields(
        RenderingContextInterface $renderingContext,
        string $configurationKey,
    ): array {
        $settings = $renderingContext->getVariableProvider()->get('settings');
        if (!\is_array($settings)) {
            throw new \UnexpectedValueException('No settings in the variable container found.', 1_651_153_736);
        }
        if (!isset($settings[$configurationKey])) {
            throw new \UnexpectedValueException(
                'No field "' . $configurationKey . '" in settings found.',
                1_651_154_598,
            );
        }

        $enabledFieldsConfiguration = $settings[$configurationKey];
        if (!\is_string($enabledFieldsConfiguration)) {
            throw new \UnexpectedValueException(
                'The setting "' . $configurationKey . '" needs to be a string.',
                1_651_155_151,
            );
        }

        return GeneralUtility::trimExplode(',', $enabledFieldsConfiguration, true);
    }
}
