<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Mapper;

use OliverKlee\Oelib\Model\FrontEndUser;

/**
 * @extends AbstractDataMapper<FrontEndUser>
 */
class FrontEndUserMapper extends AbstractDataMapper
{
    protected $tableName = 'fe_users';

    protected $modelClassName = FrontEndUser::class;
}
