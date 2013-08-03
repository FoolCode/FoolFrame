<?php

namespace Foolz\Foolframe\Model;

use \Foolz\Foolframe\Model\DoctrineConnection as DC;
use \Foolz\Cache\Cache;
use \Foolz\Plugin\Loader;

class PluginException extends \Exception {}

class Plugins
{
    /**
     * The Plugin loader object
     *
     * @var  \Foolz\Plugin\Loader
     */
    protected static $loader;

    protected static $framework;

    /**
     * The modules in FuelPHP
     *
     * @var  array
     */
    protected static $modules = [];

    protected static $_admin_sidebars = [];

    public static function instantiate(Context $framework)
    {
        static::$loader = new Loader();

        // store all the relevant data from the modules
        foreach (Legacy\Config::get('foolz/foolframe', 'config', 'modules.installed') as $module) {
            $dir = VENDPATH.$module.'/'. Legacy\Config::get($module, 'package', 'directories.plugins');
            static::$loader->addDir($dir);
            $dir = VAPPPATH.$module.'/plugins';
            static::$loader->addDir($dir);
        }

        // public dir for plugins
        static::$loader->addDir(VAPPPATH.'foolz/foolframe/plugins/');

        foreach (static::getEnabled() as $enabled) {
            try {
                $plugin = static::$loader->get($enabled['slug']);
                $plugin->bootstrap();
                // we could use execute() but we want to inject more in the call
                \Foolz\Plugin\Hook::forge('Foolz\Plugin\Plugin::execute.'.$plugin->getConfig('name'))
                    ->setObject($plugin)
                    ->setParam('framework', $framework)
                    ->execute();

                static::$loader->get($enabled['slug'])->enabled = true;
            } catch (\OutOfBoundsException $e) {

            }
        }
    }

    public static function handleWeb() {
        static::$loader->setPublicDir(DOCROOT.'foolframe/');
        static::$loader->setBaseUrl(\Uri::base().'foolframe/');
    }

    public static function clearCache()
    {
        Cache::item('foolframe.model.plugins.get_all.query')->delete();
        Cache::item('foolframe.model.plugins.get_enabled.query')->delete();
    }

    public static function getAll()
    {
        return static::$loader->getAll();
    }

    public static function getEnabled()
    {
        try {
            $result = Cache::item('foolframe.model.plugins.get_enabled.query')->get();
        } catch (\OutOfBoundsException $e) {
            $result = DC::qb()
                ->select('*')
                ->from(DC::p('plugins'), 'p')
                ->where('enabled = :enabled')
                ->setParameter(':enabled', 1)
                ->execute()
                ->fetchAll();

            Cache::item('foolframe.model.plugins.get_enabled.query')->set($result, 3600);
        }

        return $result;
    }

    public static function getInstalled()
    {
        return DC::qb()
            ->select('*')
            ->from(DC::p('plugins'), 'p')
            ->execute()
            ->fetchAll();
    }

    public static function getPlugin($slug)
    {
        return static::$loader->get($slug);
    }

    public static function enable($slug)
    {
        $plugin = static::$loader->get($slug);

        $count = DC::qb()
            ->select('COUNT(*) as count')
            ->from(DC::p('plugins'), 'p')
            ->andWhere('slug = :slug')
            ->setParameters([':slug' => $slug])
            ->execute()
            ->fetch()['count'];

        // if the plugin isn't installed yet, we will run install.php and NOT enable.php
        if (!$count) {
            return static::install($slug);
        }

        DC::qb()
            ->update(DC::p('plugins'))
            ->set('enabled', ':enabled')
            ->where('slug = :slug')
            ->setParameters(['enabled' => 1, ':slug' => $slug])
            ->execute();

        static::clearCache();
    }

    /**
     * Disables plugin and runs plugin_disable()
     */
    public static function disable($slug)
    {
        $plugin = static::$loader->get($slug);
        $dir = $plugin->getDir();

        if (file_exists($dir.'disable.php')) {
            \Fuel::load($dir.'disable.php');
        }

        DC::qb()
            ->update(DC::p('plugins'))
            ->set('enabled', ':enabled')
            ->where('slug = :slug')
            ->setParameters([':enabled' => 0, ':slug' => $slug])
            ->execute();

        static::clearCache();
    }

    public static function install($slug)
    {
        $plugin = static::$loader->get($slug);
        $plugin->install();

        DC::forge()->insert(DC::p('plugins'), ['slug' => $slug, 'enabled' => 1]);

        static::clearCache();

        // run the schema update
        $sm = \Foolz\Foolframe\Model\SchemaManager::forge(DC::forge(), DC::getPrefix().'plugin_');

        foreach (static::getInstalled() as $enabled) {
            try {
                $plug = static::$loader->get($enabled['slug']);

                if (!$plug->isBootstrapped()) {
                    $plug->bootstrap();
                }

                \Foolz\Plugin\Hook::forge('Foolz\Foolframe\Model\Plugin::schemaUpdate.'.$plug->getConfig('name'))
                    ->setParam('schema', $sm->getCodedSchema())
                    ->execute();
            } catch (\OutOfBoundsException $e) {

            }
        }

        $sm->commit();

        static::clearCache();
    }

    public static function uninstall($slug)
    {
        $dir = static::getPluginDir($slug);

        if (file_exists($dir.'uninstall.php')) {
            \Fuel::load($dir.'uninstall.php');
        }

        DC::qb()
            ->delete(DC::p('plugins'))
            ->andWhere('slug = :slug')
            ->setParameters([':slug' => $slug])
            ->execute();

        static::clearCache();
    }

    public static function getSidebarElements($type)
    {
        if (!isset(static::$_admin_sidebars[$type])) {
            return [];
        }

        return static::$_admin_sidebars[$type];
    }

    public static function registerSidebarElement($type, $section, $array = null)
    {
        // the user can also send an array with the index inseted in $section
        if(!is_null($array)) {
            $array2 = [];
            $array2[$section] = $array;
            $array = $array2;
        } else {
            $array = $section;
        }

        static::$_admin_sidebars[$type][] = $array;

        \Foolz\Foolframe\Controller\Admin::add_sidebar_element($array);
    }
}
