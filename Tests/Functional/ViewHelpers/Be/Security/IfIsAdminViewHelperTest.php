<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\ViewHelpers\Be\Security;

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Oelib\ViewHelpers\Be\Security\IfIsAdminViewHelper
 */
final class IfIsAdminViewHelperTest extends FunctionalTestCase
{
    protected bool $initializeDatabase = false;

    protected array $testExtensionsToLoad = ['oliverklee/oelib'];

    private Context $context;

    protected function setUp(): void
    {
        parent::setUp();
        $this->context = $this->get(Context::class);
    }

    private function renderViewHelper(string $html): string
    {
        $view = new StandaloneView();
        $view->setTemplateSource($this->embedInHtmlWithNamespace($html));

        return $view->render();
    }

    private function embedInHtmlWithNamespace(string $html): string
    {
        return '<html xmlns:oelib="OliverKlee\Oelib\ViewHelpers" data-namespace-typo3-fluid="true">' .
            $html . '</html>';
    }

    /**
     * @test
     */
    public function renderForAdminLoggedInRendersThenChild(): void
    {
        $user = self::createStub(BackendUserAuthentication::class);
        $user->method('isAdmin')->willReturn(true);
        $GLOBALS['BE_USER'] = $user;
        $userAspect = new UserAspect($user);
        $this->context->setAspect('backend.user', $userAspect);

        $html = '<oelib:be.security.ifIsAdmin then="THEN" else="ELSE"/>';
        $result = $this->renderViewHelper($html);

        self::assertStringContainsString('THEN', $result);
    }

    /**
     * @test
     */
    public function renderForNonAdminLoggedInRendersElseChild(): void
    {
        $user = self::createStub(BackendUserAuthentication::class);
        $user->method('isAdmin')->willReturn(false);
        $GLOBALS['BE_USER'] = $user;
        $userAspect = new UserAspect($user);
        $this->context->setAspect('backend.user', $userAspect);

        $html = '<oelib:be.security.ifIsAdmin then="THEN" else="ELSE"/>';
        $result = $this->renderViewHelper($html);

        self::assertStringContainsString('ELSE', $result);
    }

    /**
     * @test
     */
    public function renderForNoBackendUserRendersElseChild(): void
    {
        unset($GLOBALS['BE_USER']);
        $this->context->unsetAspect('backend.user');

        $html = '<oelib:be.security.ifIsAdmin then="THEN" else="ELSE"/>';
        $result = $this->renderViewHelper($html);

        self::assertStringContainsString('ELSE', $result);
    }
}
