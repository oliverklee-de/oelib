<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\ViewHelpers;

use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use TYPO3Fluid\Fluid\Core\Parser\Exception as FluidParserException;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;

/**
 * @covers \OliverKlee\Oelib\ViewHelpers\IsFieldEnabledViewHelper
 */
final class IsFieldEnabledViewHelperTest extends FunctionalTestCase
{
    protected bool $initializeDatabase = false;

    protected array $testExtensionsToLoad = ['oliverklee/oelib'];

    private StandardVariableProvider $variableProvider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->variableProvider = new StandardVariableProvider();
    }

    private function renderViewHelper(string $html): string
    {
        $view = new StandaloneView();

        $renderingContext = $view->getRenderingContext();
        $renderingContext->setVariableProvider($this->variableProvider);
        $view->setRenderingContext($renderingContext);

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
    public function renderForMissingSettingsThrowsException(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('No settings in the variable container found.');
        $this->expectExceptionCode(1_651_153_736);

        $this->renderViewHelper('<oelib:isFieldEnabled fieldName="foo"/>');
    }

    /**
     * @test
     */
    public function renderForMissingSettingNameInSettingsThrowsException(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('No field "fieldsToShow" in settings found.');
        $this->expectExceptionCode(1_651_154_598);

        $this->variableProvider->add('settings', []);

        $this->renderViewHelper('<oelib:isFieldEnabled fieldName="foo"/>');
    }

    /**
     * @return array<string, array{0: list<mixed>|positive-int}>
     */
    public static function nonStringSettingDataProvider(): array
    {
        return [
            'array' => [[]],
            'int' => [5],
        ];
    }

    /**
     * @test
     *
     * @param array{0: list<mixed>|positive-int} $value
     *
     * @dataProvider nonStringSettingDataProvider
     */
    public function renderForNonStringSettingNameInSettingsThrowsException($value): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('The setting "fieldsToShow" needs to be a string.');
        $this->expectExceptionCode(1_651_155_151);

        $this->variableProvider->add('settings', ['fieldsToShow' => $value]);

        $this->renderViewHelper('<oelib:isFieldEnabled fieldName="company"/>');
    }

    /**
     * @test
     */
    public function renderForMissingFieldNameThrowsException(): void
    {
        $this->expectException(FluidParserException::class);
        $this->expectExceptionMessage('Required argument "fieldName" was not supplied.');
        $this->expectExceptionCode(1237823699);

        $this->variableProvider->add('settings', ['fieldsToShow' => 'company']);

        $this->renderViewHelper('<oelib:isFieldEnabled />');
    }

    /**
     * @test
     */
    public function renderForEmptyFieldNameThrowsException(): void
    {
        $this->expectExceptionCode(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The argument "fieldName" must not be empty.');
        $this->expectExceptionCode(1_651_155_957);

        $this->variableProvider->add('settings', ['fieldsToShow' => 'company']);

        $this->renderViewHelper('<oelib:isFieldEnabled fieldName=""/>');
    }

    /**
     * @test
     */
    public function renderForNonStringFieldNameThrowsException(): void
    {
        $this->expectExceptionCode(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The argument "fieldName" must be a string, but was array');
        $this->expectExceptionCode(1_651_496_544);

        $this->variableProvider->add('settings', ['fieldsToShow' => 'company']);

        $this->renderViewHelper('<oelib:isFieldEnabled fieldName="{0: 1}"/>');
    }

    /**
     * @test
     */
    public function renderForSingleRequestedFieldEnabledRendersThenChild(): void
    {
        $this->variableProvider->add('settings', ['fieldsToShow' => 'company']);

        $html = '<oelib:isFieldEnabled fieldName="company" then="THEN" else="ELSE"/>';
        $result = $this->renderViewHelper($html);

        self::assertStringContainsString('THEN', $result);
    }

    /**
     * @test
     */
    public function renderForRequestedFieldEnabledWithOtherAfterRendersThenChild(): void
    {
        $this->variableProvider->add('settings', ['fieldsToShow' => 'company,name']);

        $html = '<oelib:isFieldEnabled fieldName="company" then="THEN" else="ELSE"/>';
        $result = $this->renderViewHelper($html);

        self::assertStringContainsString('THEN', $result);
    }

    /**
     * @test
     */
    public function renderForRequestedFieldEnabledWithOtherBeforeRendersThenChild(): void
    {
        $this->variableProvider->add('settings', ['fieldsToShow' => 'name,company']);

        $html = '<oelib:isFieldEnabled fieldName="company" then="THEN" else="ELSE"/>';
        $result = $this->renderViewHelper($html);

        self::assertStringContainsString('THEN', $result);
    }

    /**
     * @test
     */
    public function renderForOneOfTwoRequestedFieldsEnabledRendersThenChild(): void
    {
        $this->variableProvider->add('settings', ['fieldsToShow' => 'company']);

        $html = '<oelib:isFieldEnabled fieldName="company|name" then="THEN" else="ELSE"/>';
        $result = $this->renderViewHelper($html);

        self::assertStringContainsString('THEN', $result);
    }

    /**
     * @test
     */
    public function renderForBothRequestedFieldsEnabledRendersThenChild(): void
    {
        $this->variableProvider->add('settings', ['fieldsToShow' => 'company,name']);

        $html = '<oelib:isFieldEnabled fieldName="company|name" then="THEN" else="ELSE"/>';
        $result = $this->renderViewHelper($html);

        self::assertStringContainsString('THEN', $result);
    }

    /**
     * @test
     */
    public function renderForRequestedFieldNotEnabledRendersElseChild(): void
    {
        $this->variableProvider->add('settings', ['fieldsToShow' => 'company']);

        $html = '<oelib:isFieldEnabled fieldName="name" then="THEN" else="ELSE"/>';
        $result = $this->renderViewHelper($html);

        self::assertStringContainsString('ELSE', $result);
    }

    /**
     * @test
     */
    public function renderForNoFieldEnabledRendersElseChild(): void
    {
        $this->variableProvider->add('settings', ['fieldsToShow' => '']);

        $html = '<oelib:isFieldEnabled fieldName="name" then="THEN" else="ELSE"/>';
        $result = $this->renderViewHelper($html);

        self::assertStringContainsString('ELSE', $result);
    }

    /**
     * @test
     */
    public function renderEscapesChildren(): void
    {
        $this->variableProvider->add('settings', ['fieldsToShow' => 'company,name']);

        $html = '<oelib:isFieldEnabled fieldName="company|name"><f:then>a&amp;b</f:then></oelib:isFieldEnabled>';
        $result = $this->renderViewHelper($html);

        self::assertStringContainsString('a&amp;b', $result);
    }

    /**
     * @test
     */
    public function renderForBothRequestedConfiguredFieldsEnabledRendersThenChild(): void
    {
        $this->variableProvider->add('settings', ['columns' => 'company,name']);

        $html = '<oelib:isFieldEnabled configurationKey="columns" fieldName="company|name" then="THEN" else="ELSE"/>';
        $result = $this->renderViewHelper($html);

        self::assertStringContainsString('THEN', $result);
    }

    /**
     * @test
     */
    public function renderForSingleRequestedConfiguredFieldEnabledRendersThenChild(): void
    {
        $this->variableProvider->add('settings', ['columns' => 'company']);

        $html = '<oelib:isFieldEnabled configurationKey="columns" fieldName="company" then="THEN" else="ELSE"/>';
        $result = $this->renderViewHelper($html);

        self::assertStringContainsString('THEN', $result);
    }

    /**
     * @test
     */
    public function renderForRequestedConfiguredFieldNotEnabledRendersElseChild(): void
    {
        $this->variableProvider->add('settings', ['columns' => 'company']);

        $html = '<oelib:isFieldEnabled configurationKey="columns" fieldName="name" then="THEN" else="ELSE"/>';
        $result = $this->renderViewHelper($html);

        self::assertStringContainsString('ELSE', $result);
    }

    /**
     * @test
     */
    public function renderForNonStringConfigurationKeyThrowsException(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('The variable "configurationKey" must be a string, but was: array');
        $this->expectExceptionCode(1743708980);

        $this->variableProvider->add('settings', ['fieldsToShow' => 'name']);

        $html = '<oelib:isFieldEnabled configurationKey="{0: 1}" fieldName="name" then="THEN" else="ELSE"/>';
        $this->renderViewHelper($html);
    }

    /**
     * @test
     */
    public function renderForEmptyConfigurationKeyThrowsException(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('The variable "configurationKey" must not be empty.');
        $this->expectExceptionCode(1743709004);

        $this->variableProvider->add('settings', ['fieldsToShow' => 'name']);

        $html = '<oelib:isFieldEnabled configurationKey="" fieldName="name" then="THEN" else="ELSE"/>';
        $this->renderViewHelper($html);
    }
}
