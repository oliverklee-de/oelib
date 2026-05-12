<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Configuration;

use OliverKlee\Oelib\Interfaces\Configuration as ConfigurationInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Exception\Page\PageNotFoundException;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * This class represents a registration that allows the storage and retrieval of configuration objects.
 */
class ConfigurationRegistry
{
    private BackendConfigurationManager $backendConfigurationManager;

    /**
     * @deprecated #2287 will be removed in oelib 7.0
     */
    private static ?ConfigurationRegistry $instance = null;

    /**
     * @var array<non-empty-string, ConfigurationInterface> already created configurations (by namespace)
     */
    private array $configurations = [];

    public function __construct(BackendConfigurationManager $backendConfigurationManager)
    {
        $this->backendConfigurationManager = $backendConfigurationManager;
    }

    /**
     * Destructs a configuration for a given namespace and drops the reference to it.
     *
     * @param non-empty-string $namespace the namespace of the configuration to drop,
     *        must have been set in this registry
     */
    private function dropConfiguration(string $namespace): void
    {
        unset($this->configurations[$namespace]);
    }

    /**
     * @deprecated #2287 will be removed in oelib 7.0; use DI instead
     */
    public static function getInstance(): self
    {
        if (!self::$instance instanceof self) {
            self::$instance = GeneralUtility::makeInstance(self::class);
        }

        return self::$instance;
    }

    /**
     * Purges the current instance so that getInstance will create a new instance.
     *
     * @deprecated #2287 will be removed in oelib 7.0
     */
    public static function purgeInstance(): void
    {
        self::$instance = null;
    }

    /**
     * Retrieves a `Configuration` by namespace.
     *
     * @param non-empty-string $namespace the name of a configuration namespace, e.g., "plugin.tx_oelib"
     *
     * @return ConfigurationInterface the configuration for the given namespace
     *
     * @see getByNamespace
     *
     * @deprecated use `getByNamespace` instead. #2290 will be removed in oelib 7.0
     */
    public static function get(string $namespace): ConfigurationInterface
    {
        return self::getInstance()->getByNamespace($namespace);
    }

    /**
     * Retrieves a `Configuration` by namespace.
     *
     * @param non-empty-string $namespace the name of a configuration namespace, e.g., "plugin.tx_oelib"
     *
     * @return ConfigurationInterface the configuration for the given namespace
     */
    public function getByNamespace(string $namespace): ConfigurationInterface
    {
        $this->checkForNonEmptyNamespace($namespace);

        if (!isset($this->configurations[$namespace])) {
            $this->configurations[$namespace] = $this->retrieveConfigurationFromTypoScriptSetup($namespace);
        }

        return $this->configurations[$namespace];
    }

    /**
     * Sets a configuration for a certain namespace.
     *
     * @param non-empty-string $namespace the namespace of the configuration to set
     */
    public function set(string $namespace, ConfigurationInterface $configuration): void
    {
        $this->checkForNonEmptyNamespace($namespace);

        if (isset($this->configurations[$namespace])) {
            $this->dropConfiguration($namespace);
        }

        $this->configurations[$namespace] = $configuration;
    }

    /**
     * Checks that $namespace is non-empty.
     *
     * @throws \InvalidArgumentException if $namespace is empty
     */
    private function checkForNonEmptyNamespace(string $namespace): void
    {
        if ($namespace === '') {
            throw new \InvalidArgumentException('$namespace must not be empty.', 1_331_318_549);
        }
    }

    /**
     * Retrieves the configuration from TypoScript setup of the current page for a given namespace.
     *
     * @param non-empty-string $namespace the namespace of the configuration to retrieve
     *
     * @return TypoScriptConfiguration the TypoScript configuration for that namespace, might be empty
     */
    private function retrieveConfigurationFromTypoScriptSetup(string $namespace): TypoScriptConfiguration
    {
        $data = $this->getCompleteTypoScriptSetup();

        foreach (\explode('.', $namespace) as $namespaceSegment) {
            $data = $data[$namespaceSegment . '.'] ?? null;
            if (!\is_array($data)) {
                $data = [];
                break;
            }
        }

        $configuration = GeneralUtility::makeInstance(TypoScriptConfiguration::class);
        $configuration->setData($data);

        return $configuration;
    }

    /**
     * Retrieves the complete TypoScript setup for the current page as a nested array.
     *
     * @return array<mixed> the TypoScriptSetup for the current page, will be empty if
     *         no page is selected or if the TypoScript setup of the page is empty
     */
    private function getCompleteTypoScriptSetup(): array
    {
        $pageUid = PageFinder::getInstance()->getPageUid();
        if ($pageUid <= 0) {
            return [];
        }

        return ((new Typo3Version())->getMajorVersion() >= 12)
            ? $this->getCompleteTypoScriptSetupForTypo3V12AndUp($pageUid)
            : $this->getCompleteTypoScriptSetupForTypo3BelowV12($pageUid);
    }

    /**
     * @param int<1, max> $pageUid
     *
     * @return array<mixed>
     */
    private function getCompleteTypoScriptSetupForTypo3V12AndUp(int $pageUid): array
    {
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        if (($request instanceof ServerRequestInterface)
            && ($request->getAttribute('applicationType') === SystemEnvironmentBuilder::REQUESTTYPE_FE)
            && ($request->getAttribute('frontend.typoscript') instanceof FrontendTypoScript)
        ) {
            return $request->getAttribute('frontend.typoscript')->getSetupArray();
        }

        if ($request instanceof ServerRequestInterface) {
            $queryParams = $request->getQueryParams();
        } else {
            $request = new ServerRequest();
            $queryParams = [];
        }
        $queryParams['id'] = $pageUid;
        $request = $request->withQueryParams($queryParams);

        $configurationManager = $this->getBackendConfigurationManager($request);

        return ((new Typo3Version())->getMajorVersion() <= 12)
            ? $configurationManager->getTypoScriptSetup()
            : $configurationManager->getTypoScriptSetup($request);
    }

    private function getBackendConfigurationManager(ServerRequestInterface $request): BackendConfigurationManager
    {
        $configurationManager = $this->backendConfigurationManager;
        if ((new Typo3Version())->getMajorVersion() <= 12) {
            $configurationManager->setRequest($request);
        }

        return $configurationManager;
    }

    /**
     * @param int<1, max> $pageUid
     *
     * @return array<mixed>
     */
    private function getCompleteTypoScriptSetupForTypo3BelowV12(int $pageUid): array
    {
        $frontEndController = $GLOBALS['TSFE'] ?? null;
        $template = $frontEndController instanceof TypoScriptFrontendController ? $frontEndController->tmpl : null;
        if ($template instanceof TemplateService && $template->loaded) {
            return $template->setup;
        }

        $template = GeneralUtility::makeInstance(TemplateService::class);
        $template->tt_track = false;

        try {
            $rootLine = GeneralUtility::makeInstance(RootlineUtility::class, $pageUid)->get();
        } catch (PageNotFoundException $pageNotFoundException) {
            $rootLine = [];
        }

        $template->runThroughTemplates($rootLine);
        $template->generateConfig();

        return $template->setup;
    }
}
