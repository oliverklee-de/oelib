<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Mapper;

use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Oelib\Model\FrontEndUser;

/**
 * @extends AbstractDataMapper<FrontEndUser>
 */
class FrontEndUserMapper extends AbstractDataMapper
{
    protected $tableName = 'fe_users';

    protected $modelClassName = FrontEndUser::class;

    // @deprecated #1992 will be removed in version 7.0
    protected $additionalKeys = ['username'];

    /**
     * Finds a front-end user by username. Hidden user records will be
     * retrieved as well.
     *
     * @param non-empty-string $username username, case-insensitive
     *
     * @return FrontEndUser model of the front-end user with the provided username
     *
     * @throws NotFoundException if there is no front-end user with the provided username in the database
     *
     * @deprecated #1992 will be removed in version 7.0
     */
    public function findByUserName(string $username): FrontEndUser
    {
        return $this->findOneByKey('username', $username);
    }
}
