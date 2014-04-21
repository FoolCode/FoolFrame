<?php

namespace Foolz\Foolframe\Model;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;

class DoctrineConnection extends Model
{
    /**
     * @var string
     */
    public $prefix = '';

    /**
     * @var \Doctrine\DBAL\Connection
     */
    public $connection;

    /**
     * Creates a new connection to the database
     *
     * @param Context $context
     * @param array|Config $db_data
     */
    public function __construct(Context $context, $db_data = [])
    {
        parent::__construct($context);

        // load the defaults if the config object has been passed
        if ($db_data instanceof Config) {
            $db_data = $db_data->get('foolz/foolframe', 'db', 'default');
        }

        $config = new Configuration();

        $config->setSQLLogger(new DoctrineLogger($context));

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

        $this->prefix = $db_data['prefix'];

        $this->connection = DriverManager::getConnection($data, $config);
    }

    /**
     * Get rid of the connection on serialization
     *
     * @return array
     */
    public function __sleep() {
        return [];
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Returns a query builder
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function qb()
    {
        return $this->connection->createQueryBuilder();
    }

    /**
     * Returns the prefix
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Adds a prefix to the table name
     *
     * @param $table
     * @return string
     */
    public function p($table = '')
    {
        return $this->prefix.$table;
    }
}
