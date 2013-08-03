<?php

namespace Foolz\Foolframe\Model\Legacy;
use Foolz\Foolframe\Model\Legacy\DoctrineLogger;
use Foolz\Foolframe\Model\Legacy;

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
     * @param  string $instance  The name of the instance
     * @param  string $from_config The config file from where to pick up connection data
     * @param  array $override  Allows overriding connection data
     *
     * @throws \DomainException If the database configuration doesn't exist
     * @return  \Doctrine\DBAL\Connection
     */
    public static function forge($instance = 'default', $from_config = 'default', $override = [])
    {
        if (isset(static::$instances[$instance])) {
            return static::$instances[$instance];
        }

        $config = new \Doctrine\DBAL\Configuration();

        $config->setSQLLogger(new DoctrineLogger());

        $db_data = Legacy\Config::get('foolz/foolframe', 'db', $from_config);

        $db_data += $override;

        if ($db_data === false) {
            throw new \DomainException('The specified database configuration is not available.');
        }

        $data = [
            'dbname' => $db_data['dbname'],
            'user' => $db_data['user'],
            'password' => $db_data['password'],
            'host' => $db_data['host'],
            'driver' => $db_data['driver'],
        ];

        if ($db_data['driver'] == 'pdo_mysql') {
            $data['charset'] = $db_data['charset'];
        }

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
