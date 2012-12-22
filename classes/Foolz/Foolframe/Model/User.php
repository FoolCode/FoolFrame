<?php

namespace Foolz\Foolframe\Model;

use \Foolz\Foolframe\Model\DoctrineConnection as DC;

class User extends \Model
{

	public $id = null;
	public $username = null;
	public $password = null;
	public $group_id = null;
	public $email = null;
	public $new_email = null;
	public $new_email_key = null;
	public $new_email_time = null;
	public $last_login = null;
	public $activated = null;
	public $activation_key = null;
	public $new_password_key = null;
	public $deletion_key = null;
	public $deletion_time = null;
	public $profile_fields = null;
	public $bio = null;
	public $twitter = null;
	public $display_name = null;
	public $created_at = null;

	public $password_current = null;

	private $editable_fields = array(
		'username',
		'password',
		'group_id',
		'email',
		'profile_fields',
		'bio',
		'twitter',
		'display_name'
	);

	public function __construct($data)
	{
		foreach($data as $key => $item)
		{
			if ($key == 'password')
			{
				$key = 'password_current';
			}
			
			$this->$key = $item;
		}
	}


	public static function forge($data)
	{
		if (is_array($data) && !\Arr::is_assoc($data))
		{
			$array = array();

			foreach($data as $item)
			{
				$array[] = static::forge($item);
			}

			return $array;
		}

		return new User($data);
	}


	public function save(Array $data = array())
	{
		foreach ($data as $key => $item)
		{
			$this->$key = $item;
		}

		$set = array();

		foreach($this->editable_fields as $filter)
		{
			$set[$filter] = $this->$filter;
		}

		if ( ! is_null($set['password']) && $set['password'] !== '')
			$set['password'] = \Auth::hash_password($set['password']);
		else
			unset($set['password']);

		$query = DC::qb()
			->update(DC::p(\Foolz\Config\Config::get('foolz/foolframe', 'foolauth', 'table_name')))
			->where('id = :id')
			->setParameter(':id', $this->id);

		foreach ($set as $key => $item)
		{
			$query->set(DC::forge()->quoteIdentifier($key), DC::forge()->quote($item));
		}

		$query->execute();
	}

}