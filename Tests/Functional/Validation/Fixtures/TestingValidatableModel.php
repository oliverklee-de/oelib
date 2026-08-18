<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Validation\Fixtures;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

final class TestingValidatableModel extends AbstractEntity
{
    public function __construct(protected string $title = '') {}

    public function getTitle(): string
    {
        return $this->title;
    }
}
