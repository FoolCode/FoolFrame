<?php

namespace Foolz\FoolFrame\Model;

use Foolz\Plugin\Hook;

class SchemaManager
{
    /**
     * The database connection
     *
     * @var  \Doctrine\DBAL\Connection
     */
    protected $connection;

    /**
     * Set a prefix to ignore all the tables that aren't prefixed by this
     *
     * @var  string
     */
    protected $prefix = null;

    /**
     * The schema that holds what the code explains
     *
     * @var  \Doctrine\DBAL\Schema\Schema
     */
    protected $coded_schema;

    /**
     * The current database schema
     *
     * @var  \Doctrine\DBAL\Schema\Schema
     */
    protected $database_schema;

    /**
     * Creates a schema manager for testing if the modules are up to date
     *
     * @param  \Doctrine\DBAL\Connection  $connection        The doctrine database connection
     * @param  string                     $prefix            The prefix used for the database (will ignore any other prefix)
     * @param  array                      $prefixes_ignored  Prefix in the database that should be ignored between the ones with the selected $prefix. Do not prepend $prefix.
     *
     * @return  \Foolz\FoolFrame\Model\SchemaManager  A new SchemaManager
     */
    public static function forge(\Doctrine\DBAL\Connection $connection, $prefix = '', $prefixes_ignored = [])
    {
        $new = new static();
        $new->connection = $connection;
        $new->prefix = $prefix;

        $sm = $new->connection->getSchemaManager();
        $tables = $sm->listTables();

        // get rid of the tables that don't have the same prefix
        if ($prefix !== null) {
            foreach ($tables as $key => $table) {
                if (strpos($table->getName(), $new->prefix) !== 0) {
                    unset($tables[$key]);
                }
            }
        }

        // get more prefixes ignored
        $prefixes_ignored = Hook::forge('Foolz\FoolFrame\Model\SchemaManager::forge#var.ignorePrefix')
            ->setObject($new)
            ->setParam('prefixes_ignored', $prefixes_ignored)
            ->execute()
            ->get($prefixes_ignored);

        // get rid of the ignored prefixes (in example ff_plugin_)
        if (count($prefixes_ignored)) {
            foreach ($tables as $key => $table) {
                foreach ($prefixes_ignored as $prefix_ignored) {
                    if (strpos($table->getName(), $new->prefix.$prefix_ignored) === 0) {
                        unset($tables[$key]);
                    }
                }
            }
        }

        // get more tables ignored
        $tables = Hook::forge('Foolz\FoolFrame\Model\SchemaManager::forge#var.tables')
            ->setObject($new)
            ->setParam('tables', $tables)
            ->execute()
            ->get($tables);

        // create a database "how it is now"
        $new->database_schema = new \Doctrine\DBAL\Schema\Schema($tables, [], $sm->createSchemaConfig());

        // make an empty schema
        $new->coded_schema = new \Doctrine\DBAL\Schema\Schema([], [], $sm->createSchemaConfig());

        return $new;
    }

    /**
     * Returns the Doctrine Connection
     *
     * @return \Doctrine\DBAL\Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Get the prefix for the database
     *
     * @return  null|string  null if there's no prefix, string if set
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Returns the database schema that has to be edited to the destination one
     *
     * @return  \Doctrine\DBAL\Schema\Schema  Returns an empty (or edited) schema for you to edit
     */
    public function getCodedSchema()
    {
        return $this->coded_schema;
    }

    /**
     * Returns the live database schema
     *
     * @return  \Doctrine\DBAL\Schema\Schema  Returns the schema that was generated out of the database
     */
    public function getDatabaseSchema()
    {
        return $this->database_schema;
    }

    /**
     * Returns the array of changes that will take place if commit is run
     *
     * @return  array  An array with SQL queries that correspond to the changes
     */
    public function getChanges()
    {
        return $this->coded_schema->getMigrateFromSql(
            $this->database_schema,
            $this->connection->getSchemaManager()->getDatabasePlatform()
        );
    }

    /**
     * Runs the changes to the schema
     */
    public function commit()
    {
        $this->connection->beginTransaction();

        foreach ($this->getChanges() as $sql) {
            $this->connection->query($sql);
        }

        $this->connection->commit();
    }
}
