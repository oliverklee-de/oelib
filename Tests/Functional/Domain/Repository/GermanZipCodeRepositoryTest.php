<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Domain\Repository;

use OliverKlee\Oelib\Domain\Model\GermanZipCode;
use OliverKlee\Oelib\Domain\Repository\GermanZipCodeRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Oelib\Domain\Model\GermanZipCode
 * @covers \OliverKlee\Oelib\Domain\Repository\GermanZipCodeRepository
 */
final class GermanZipCodeRepositoryTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = ['oliverklee/oelib'];

    private GermanZipCodeRepository $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = $this->get(GermanZipCodeRepository::class);

        $this->importCSVDataSet(__DIR__ . '/Fixtures/ZipCodes.csv');
    }

    #[Test]
    public function isRepository(): void
    {
        self::assertInstanceOf(Repository::class, $this->subject);
    }

    #[Test]
    public function addThrowsException(): void
    {
        $this->expectException(\BadMethodCallException::class);

        $this->subject->add(new GermanZipCode());
    }

    #[Test]
    public function removeThrowsException(): void
    {
        $this->expectException(\BadMethodCallException::class);

        $this->subject->remove(new GermanZipCode());
    }

    #[Test]
    public function updateThrowsException(): void
    {
        $this->expectException(\BadMethodCallException::class);

        $this->subject->update(new GermanZipCode());
    }

    #[Test]
    public function removeAllThrowsException(): void
    {
        $this->expectException(\BadMethodCallException::class);

        $this->subject->removeAll();
    }

    #[Test]
    public function mapsAllModelFields(): void
    {
        $result = $this->subject->findByUid(9000);

        self::assertInstanceOf(GermanZipCode::class, $result);
        self::assertSame('01067', $result->getZipCode());
        self::assertSame('Dresden', $result->getCityName());
        self::assertSame(13.721068, $result->getLongitude());
        self::assertSame(51.060036, $result->getLatitude());
    }

    #[Test]
    public function findOneByZipCodeWithMatchReturnsMatch(): void
    {
        $zipCode = '01067';
        $result = $this->subject->findOneByZipCode($zipCode);

        self::assertInstanceOf(GermanZipCode::class, $result);
        self::assertSame($zipCode, $result->getZipCode());
        self::assertSame('Dresden', $result->getCityName());
    }

    #[Test]
    public function findOneByZipCodeWithMatchCalledTwoTimesReturnsTheSameModel(): void
    {
        $zipCode = '01067';
        $firstResult = $this->subject->findOneByZipCode($zipCode);
        $secondResult = $this->subject->findOneByZipCode($zipCode);

        self::assertSame($firstResult, $secondResult);
    }

    /**
     * @return string[][]
     */
    public static function nonMatchedZipCodesDataProvider(): array
    {
        return [
            '5 digits without match' => ['00000'],
            '5 letters' => ['av3sd'],
            '4 digits' => ['1233'],
            '6 digits' => ['463726'],
            'empty string' => [''],
        ];
    }

    #[Test]
    #[DataProvider('nonMatchedZipCodesDataProvider')]
    public function findOneByZipCodeWithoutMatchReturnsNull(string $zipCode): void
    {
        $result = $this->subject->findOneByZipCode($zipCode);

        self::assertNull($result);
    }
}
