<?php

namespace Foolz\Foolframe\Model;

class UsersWrongIdException extends \FuelException {}

class Users
{
	/**
	 * Gets the current user
	 *
	 * @param  int  $id
	 * @return object
	 */
	public static function get_user()
	{
		$id = \Auth::get_user_id();
		$id = $id[1];

		$result = \DC::qb()
			->select('*')
			->from(\DC::p(\Foolz\Config\Config::get('foolz/foolframe', 'foolauth', 'table_name')), 't')
			->where('t.id = :id')
			->setParameter(':id', $id)
			->execute()
			->fetch();

		if ( ! $result)
		{
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
	public static function get_user_by($field, $id)
	{
		$result = \DC::qb()
			->select('*')
			->from(\DC::p(\Foolz\Config\Config::get('foolz/foolframe', 'foolauth', 'table_name')), 't')
			->where($field.' = '.\DC::forge()->quote($id))
			->execute()
			->fetch();

		if ( ! $result)
		{
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
	public static function get_all($page = 1, $limit = 40)
	{
		$users = \DC::qb()
			->select('*')
			->from(\DC::p(\Foolz\Config\Config::get('foolz/foolframe', 'foolauth', 'table_name')), 't')
			->setMaxResults($limit)
			->setFirstResult(($page * $limit) - $limit)
			->execute()
			->fetchAll();

		$users = User::forge($users);

		$count = \DC::qb()
			->select('COUNT(*) as count')
			->from(\DC::p(\Foolz\Config\Config::get('foolz/foolframe', 'foolauth', 'table_name')), 't')
			->execute()
			->fetch();

		return array('result' => $users, 'count' => $count['count']);
	}

}

/* end of file user.php */