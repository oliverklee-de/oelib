<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Configuration;

use OliverKlee\Oelib\DataStructures\AbstractReadOnlyObjectWithPublicAccessors;
use OliverKlee\Oelib\Interfaces\Configuration as ConfigurationInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * This class provides access to the configuration in the Flexforms of the given content object,
 * but not to TypoScript configuration. It can access data from any Flexforms sheet (without the need to
 * provide the sheet name for the access).
 */
class FlexformsConfiguration extends AbstractReadOnlyObjectWithPublicAccessors implements ConfigurationInterface
{
    private ?\DOMXPath $xPath = null;

    /**
     * @var array<string, mixed>|null
     */
    private ?array $data = null;

    public function __construct(ContentObjectRenderer $contentObject)
    {
        $data = $contentObject->data['pi_flexform'] ?? null;
        if (\is_string($data) && $data !== '') {
            $this->parseXmlIntoDocument($data);
        } elseif (\is_array($data)) {
            $this->data = $data;
        }
    }

    private function parseXmlIntoDocument(string $flexFormsXml): void
    {
        $document = new \DOMDocument();
        $libXmlState = \libxml_use_internal_errors(true);
        $document->loadXML($flexFormsXml);
        if (\libxml_get_errors() === []) {
            $this->xPath = new \DOMXPath($document);
        }

        \libxml_clear_errors();
        \libxml_use_internal_errors($libXmlState);
    }

    /**
     * Returns the name of the configuration source, e.g., "TypoScript setup" or "Flexforms".
     *
     * This name may also contain HTML.
     *
     * @return non-empty-string
     */
    public function getSourceName(): string
    {
        return 'in the plugin Flexforms';
    }

    protected function get(string $key): ?string
    {
        if ($this->xPath instanceof \DOMXPath) {
            $value = $this->getFromXml($key);
        } elseif (\is_array($this->data)) {
            $value = $this->getFromArray($this->data, $key);
        } else {
            $value = null;
        }

        return $value;
    }

    private function getFromXml(string $key): ?string
    {
        if (!$this->xPath instanceof \DOMXPath) {
            throw new \BadMethodCallException('$this->xPath is not initialized.', 1_645_719_281);
        }

        $matchingNodes = $this->xPath->query(
            sprintf(
                '/T3FlexForms/data/sheet/language/field[@index=\'%s\']/value',
                $key,
            ),
        );
        $firstMatchingNode = $matchingNodes instanceof \DOMNodeList ? $matchingNodes->item(0) : null;

        return $firstMatchingNode instanceof \DOMNode ? $firstMatchingNode->textContent : null;
    }

    /**
     * @param array<array-key, array<string|int, mixed>|int|string> $haystack
     */
    private function getFromArray(array $haystack, string $needleKey): ?string
    {
        $value = null;
        foreach ($haystack as $contents) {
            // We expect nested array, but let's safeguard against bogus data.
            if (!\is_array($contents)) {
                continue;
            }

            // Do we have a direct match?
            if (isset($contents[$needleKey])) {
                $candidate = $this->getFirstElementOfPotentialArray($contents[$needleKey]);
                if (\is_string($candidate) || \is_int($candidate)) {
                    $value = (string)$candidate;
                    break;
                }
            }

            // No match … then let's recurse.
            $value = $this->getFromArray($contents, $needleKey);
            // If there is a match now, stop.
            if (\is_string($value)) {
                break;
            }
        }

        return $value;
    }

    private function getFirstElementOfPotentialArray(mixed $array): mixed
    {
        if (!\is_array($array)) {
            return null;
        }

        \reset($array);

        return \current($array);
    }
}
