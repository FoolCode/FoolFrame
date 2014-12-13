<?php

namespace Foolz\Foolframe\Model;

use Foolz\Foolframe\Model\Legacy\DoctrineConnection as DC;
use \Foolz\Cache\Cache;
use \Foolz\Plugin\Loader;

class PluginException extends \Exception {}

class Plugins extends Model
{
    /**
     * The Plugin loader object
     *
     * @var  \Foolz\Plugin\Loader
     */
    protected $loader;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Uri
     */
    protected $uri;

    /**
     * @var DoctrineConnection
     */
    protected $dc;

    public function __construct(Context $context)
    {
        parent::__construct($context);
        $this->config = $context->getService('config');
        $this->dc = $context->getService('doctrine');
    }

    public function instantiate()
    {
        $this->loader = new Loader();

        // store all the relevant data from the modules
        foreach ($this->config->get('foolz/foolframe', 'config', 'modules.installed') as $module) {
            $dir = $this->config->get($module, 'package', 'directories.plugins');
            $this->loader->addDir($dir);
            $dir = VAPPPATH.$module.'/plugins';
            $this->loader->addDir($dir);
        }

        // public dir for plugins
        $this->loader->addDir(VAPPPATH.'foolz/foolframe/plugins/');

        foreach ($this->getEnabled() as $enabled) {
            try {
                $plugin = $this->loader->get($enabled['slug']);
                $plugin->bootstrap();
                // we could use execute() but we want to inject more in the call
                \Foolz\Plugin\Hook::forge('Foolz\Plugin\Plugin::execute.'.$plugin->getConfig('name'))
                    ->setObject($plugin)
                    ->setParam('framework', $this->getContext())
                    ->setParam('context', $this->getContext())
                    ->execute();

                $this->loader->get($enabled['slug'])->enabled = true;
            } catch (\OutOfBoundsException $e) {

            }
        }
    }

    public function handleWeb() {
        $this->uri = $this->getContext()->getService('uri');
        $this->loader->setPublicDir(DOCROOT.'foolframe/');
        $this->loader->setBaseUrl($this->uri->base().'foolframe/');
    }

    public function clearCache()
    {
        Cache::item('foolframe.model.plugins.get_all.query')->delete();
        Cache::item('foolframe.model.plugins.get_enabled.query')->delete();
    }

    public function getAll()
    {
        return $this->loader->getAll();
    }

    public function getEnabled()
    {
        try {
            $result = Cache::item('foolframe.model.plugins.get_enabled.query')->get();
        } catch (\OutOfBoundsException $e) {
            $result = $this->dc->qb()
                ->select('*')
                ->from($this->dc->p('plugins'), 'p')
                ->where('enabled = :enabled')
                ->setParameter(':enabled', 1)
                ->execute()
                ->fetchAll();

            Cache::item('foolframe.model.plugins.get_enabled.query')->set($result, 3600);
        }

        return $result;
    }

    public function getInstalled()
    {
        return $this->dc->qb()
            ->select('*')
            ->from($this->dc->p('plugins'), 'p')
            ->execute()
            ->fetchAll();
    }

    public function getPlugin($slug)
    {
        return $this->loader->get($slug);
    }

    public function enable($slug)
    {
        $plugin = $this->loader->get($slug);

        $count = $this->dc->qb()
            ->select('COUNT(*) as count')
            ->from($this->dc->p('plugins'), 'p')
            ->andWhere('slug = :slug')
            ->setParameters([':slug' => $slug])
            ->execute()
            ->fetch()['count'];

        // if the plugin isn't installed yet, we will run install.php and NOT enable.php
        if (!$count) {
            $this->install($slug);
            return;
        }

        $this->dc->qb()
            ->update($this->dc->p('plugins'))
            ->set('enabled', ':enabled')
            ->where('slug = :slug')
            ->setParameters(['enabled' => 1, ':slug' => $slug])
            ->execute();

        $this->clearCache();
    }

    /**
     * Disables plugin and runs plugin_disable()
     */
    public function disable($slug)
    {
        $plugin = $this->loader->get($slug);

        $this->dc->qb()
            ->update($this->dc->p('plugins'))
            ->set('enabled', ':enabled')
            ->where('slug = :slug')
            ->setParameters([':enabled' => 0, ':slug' => $slug])
            ->execute();

        $this->clearCache();
    }

    public function install($slug)
    {
        $plugin = $this->loader->get($slug);
        $plugin->install();

        $this->dc->getConnection()->insert($this->dc->p('plugins'), ['slug' => $slug, 'enabled' => 1]);

        $this->clearCache();

        // run the schema update
        $sm = \Foolz\Foolframe\Model\SchemaManager::forge($this->dc->getConnection(), $this->dc->getPrefix().'plugin_');

        foreach ($this->getInstalled() as $enabled) {
            try {
                $plug = $this->loader->get($enabled['slug']);

                if (!$plug->isBootstrapped()) {
                    $plug->bootstrap();
                }

                \Foolz\Plugin\Hook::forge('Foolz\Foolframe\Model\Plugin::schemaUpdate.'.$plug->getConfig('name'))
                    ->setParam('context', $this->getContext())
                    ->setParam('schema', $sm->getCodedSchema())
                    ->execute();
            } catch (\OutOfBoundsException $e) {

            }
        }

        $sm->commit();

        $this->clearCache();
    }
}
