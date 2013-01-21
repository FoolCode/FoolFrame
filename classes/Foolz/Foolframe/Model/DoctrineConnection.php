<?php

namespace Foolz\Foolframe\Model;

/**
 * Doctrine Connection Manager for FoolFrame
 */
class DoctrineConnection
{
	/**
	 * The connections to the database
	 *
	 * @var  array
	 */
	protected static $instances = [];

	/**
	 * The prefixes by instance
	 *
	 * @var  array
	 */
	protected static $prefixes = [];

	/**
	 * Creates a new \Doctrine\DBAL\Connection or returns the existing instance
	 *
	 * @param  string  $instance  The name of the instance
	 *
	 * @return  \Doctrine\DBAL\Connection
	 * @throws  \DomainException  If the database configuration doesn't exist
	 */
	public static function forge($instance = 'default')
	{
		if (isset(static::$instances[$instance]))
		{
			return static::$instances[$instance];
		}

		$config = new \Doctrine\DBAL\Configuration();

		$db_data = \Foolz\Config\Config::get('foolz/foolframe' , 'db', $instance);

		if ($db_data === false)
		{
			throw new \DomainException('The specified database configuration is not available.');
		}

		$data = [
			'dbname' => $db_data['dbname'],
			'user' => $db_data['user'],
			'password' => $db_data['password'],
			'host' => $db_data['host'],
			'driver' => $db_data['driver'],
		];

		static::$prefixes[$instance] = $db_data['prefix'];

		return static::$instances[$instance] = \Doctrine\DBAL\DriverManager::getConnection($data, $config);
	}

	/**
	 * Returns a Query Builder
	 *
	 * @param  string  $instance  The named instance
	 *
	 * @return  \Doctrine\DBAL\Query\QueryBuilder
	 * @throws  \DomainException  If the database configuration doesn't exist
	 */
	public static function qb($instance = 'default')
	{
		return static::forge($instance)->createQueryBuilder();
	}

	/**
	 * Returns the prefix
	 *
	 * @param  string  $instance  The named instance
	 *
	 * @return string  The prefix for the instance
	 */
	public static function getPrefix($instance = 'default')
	{
		return static::$prefixes[$instance];
	}

	/**
	 * Returns the table name with the prefix
	 *
	 * @param   string  $table The table name
	 * @param   string  $instance  The named instance
	 *
	 * @return  string the table name with the prefix
	 */
	public static function p($table, $instance = 'default')
	{
		return static::$prefixes[$instance].$table;
	}
}