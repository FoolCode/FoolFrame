<?php

namespace Foolz\Foolframe\Model;

use Foolz\Foolframe\Model\Legacy\Config;
use Foolz\Foolframe\Model\Legacy\DoctrineConnection as DC;

class UsersWrongIdException extends \Exception {}

class Users
{
    /**
     * Gets the current user
     *
     * @param  int  $id
     * @return object
     */
    public static function getUser()
    {
        $id = \Auth::get_user_id();
        $id = $id[1];

        $result = DC::qb()
            ->select('*')
            ->from(DC::p(Legacy\Config::get('foolz/foolframe', 'foolauth', 'table_name')), 't')
            ->where('t.id = :id')
            ->setParameter(':id', $id)
            ->execute()
            ->fetch();

        if (!$result) {
            throw new UsersWrongIdException;
        }

        return User::forge($result);
    }

    /**
     * Gets single user database row by selected row
     *
     * @param  int  $id
     * @return object
     */
    public static function getUserBy($field, $id)
    {
        $result = DC::qb()
            ->select('*')
            ->from(DC::p(Config::get('foolz/foolframe', 'foolauth', 'table_name')), 't')
            ->where($field.' = '.DC::forge()->quote($id))
            ->execute()
            ->fetch();

        if (!$result) {
            throw new UsersWrongIdException;
        }

        return User::forge($result);
    }

    /**
     * Gets all user limited with page and limit
     *
     * @param  int  $page
     * @param  into $limit
     * @return object
     */
    public static function getAll($page = 1, $limit = 40)
    {
        $users = DC::qb()
            ->select('*')
            ->from(DC::p(Config::get('foolz/foolframe', 'foolauth', 'table_name')), 't')
            ->setMaxResults($limit)
            ->setFirstResult(($page * $limit) - $limit)
            ->execute()
            ->fetchAll();

        $users = User::forge($users);

        $count = DC::qb()
            ->select('COUNT(*) as count')
            ->from(DC::p(Legacy\Config::get('foolz/foolframe', 'foolauth', 'table_name')), 't')
            ->execute()
            ->fetch();

        return ['result' => $users, 'count' => $count['count']];
    }
}
