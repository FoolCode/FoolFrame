<?php

namespace Foolz\Foolframe\Model;

use Foolz\Foolframe\Model\Config;

class Install extends Model
{
    /**
     * @var Config
     */
    protected $config;

    public function __construct(Context $context)
    {
        parent::__construct($context);

        $this->config = $context->getService('config');
    }

    public static function check_database($array)
    {
        switch ($array['type']) {
            case 'pdo_mysql':
                try {
                    new \PDO(
                        'mysql:dbname='.$array['database'].';host='.$array['hostname'],
                        $array['username'],
                        $array['password']
                    );

                    return true;
                } catch (\PDOException $e) {
                    return false;
                }
            case 'pdo_pgsql':
                try {
                    new \PDO(
                        'pgsql:dbname='.$array['database'].';host='.$array['hostname'],
                        $array['username'],
                        $array['password']
                    );

                    return true;
                } catch (\PDOException $e) {
                    return false;
                }
        }
    }

    public function setup_database($array)
    {
        $this->config->set('foolz/foolframe', 'db', 'default', array(
            'driver' => $array['type'],
            'host' => $array['hostname'],
            'port' => '3306',
            'dbname' => $array['database'],
            'user' => $array['username'],
            'password' => $array['password'],
            'prefix' => $array['prefix'],
            'charset' => 'utf8mb4',
        ));

        $this->config->save('foolz/foolframe', 'db');
    }

    public function create_salts()
    {
        // config without slash is the custom foolz one
        $this->config->set('foolz/foolframe', 'config', 'config.cookie_prefix', 'foolframe_'.Util::randomString(3).'_');
        $this->config->save('foolz/foolframe', 'config');

        $this->config->set('foolz/foolframe', 'cache', 'prefix', 'foolframe_'.Util::randomString(3).'_');
        $this->config->save('foolz/foolframe', 'cache');
    }

    public function install_modules()
    {
        $this->config->addPackage('unknown', ASSETSPATH);
        $class_name = $this->config->get('unknown', 'package', 'main.class_name');
        $name_lowercase = strtolower($class_name);

        $modules = [$name_lowercase => 'foolz/'.$name_lowercase];

        $dc = new DoctrineConnection($this->getContext(), $this->config);
        $sm = SchemaManager::forge($dc->getConnection(), $dc->getPrefix());
        Schema::load($this->getContext(), $sm);

        $schema_class = '\\Foolz\\'.$class_name.'\\Model\\Schema';
        $schema_class::load($this->getContext(), $sm);

        $sm->commit();

        $this->config->set('foolz/foolframe', 'config', 'modules.installed', $modules);
        $this->config->set('foolz/foolframe', 'config', 'install.installed', true);
        $this->config->save('foolz/foolframe', 'config');
    }
}
