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
        $this->config->set('foolz/foolframe', 'config', 'config.cookie_prefix', 'foolframe_'.\Str::random('alnum', 3).'_');
        $this->config->save('foolz/foolframe', 'config');

        $this->config->set('foolz/foolframe', 'foolauth', 'salt', \Str::random('alnum', 24));
        $this->config->set('foolz/foolframe', 'foolauth', 'login_hash_salt', \Str::random('alnum', 24));
        $this->config->save('foolz/foolframe', 'foolauth');

        $this->config->set('foolz/foolframe', 'cache', 'prefix', 'foolframe_'.\Str::random('alnum', 3).'_');
        $this->config->save('foolz/foolframe', 'cache');
    }

    public function modules()
    {
        $modules = array(
            'foolfuuka' => array(
                'title' => 'FoolFuuka Imageboard',
                'description' => _i('FoolFuuka is one of the most advanced imageboard software written.'),
                'disabled' => false,
            ),

            'foolslide' => array(
                'title' => 'FoolSlide Online Reader',
                'description' => _i('FoolSlide provides a clean visual interface to view multiple images in reading format. It can be used standalone to offer users the best reading experience available online.'),
                'disabled' => true,
            ),

            'foolstatus' => array(
                'title' => _i('FoolStatus'),
                'description' => _i('FoolStatus is an open-source status dashboard that allows content providers to alert users of network interruptions.'),
                'disabled' => false
            )
        );

        return $modules;
    }
}
