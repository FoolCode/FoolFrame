<?php

namespace Foolz\Foolframe\Model;

use \Foolz\Plugin\Loader;
use \Foolz\Foolframe\Model\DoctrineConnection as DC;
use \Foolz\Cache\Cache;

class PluginException extends \FuelException {}

class Plugins
{
	/**
	 * The Plugin loader object
	 *
	 * @var  \Foolz\Plugin\Loader
	 */
	protected static $loader;

	/**
	 * The modules in FuelPHP
	 *
	 * @var  array
	 */
	protected static $modules = [];


	protected static $_admin_sidebars = [];

	protected static $_identifiers = [];

	public static function initialize()
	{
		static::$loader = new Loader();

		// store all the relevant data from the modules
		foreach (\Foolz\Config\Config::get('foolz/foolframe', 'config', 'modules.installed') as $module)
		{
			$dir = VENDPATH.$module.'/'.\Foolz\Config\Config::get($module, 'package', 'directories.plugins');
			static::$_identifiers[$module] = $dir;
			static::$loader->addDir($module, $dir);
		}

		foreach (static::get_enabled() as $enabled)
		{
			try
			{
				static::$loader->get($enabled['identifier'], $enabled['slug'])->execute();
				static::$loader->get($enabled['identifier'], $enabled['slug'])->enabled = true;
			}
			catch (\OutOfBoundsException $e)
			{}
		}

	}

	public static function clear_cache()
	{
		Cache::item('ff.model.plugins.get_all.query')->delete();
		Cache::item('ff.model.plugins.get_enabled.query')->delete();
	}

	public static function get_all()
	{
		return static::$loader->getAll();
	}

	public static function get_enabled()
	{
		try
		{
			$result = Cache::item('ff.model.plugins.get_enabled.query')->get();
		}
		catch (\OutOfBoundsException $e)
		{
			$result = DC::qb()
				->select('*')
				->from(DC::p('plugins'), 'p')
				->where('enabled = :enabled')
				->setParameter(':enabled', true)
				->execute()
				->fetchAll();

			Cache::item('ff.model.plugins.get_enabled.query')->set($result, 3600);
		}

		return $result;
	}

	public static function get_installed()
	{
		return DC::qb()
			->select('*')
			->from(DC::p('plugins'), 'p')
			->execute()
			->fetchAll();
	}

	/**
	 *
	 * @param type $module
	 * @param type $slug
	 *
	 * @return \Foolz\Plugin\Plugin
	 */
	public static function get_plugin($module, $slug)
	{
		return static::$loader->get($module, $slug);
	}

	public static function enable($module, $slug)
	{
		$plugin = static::$loader->get($module, $slug);

		$count = DC::qb()
			->select('COUNT(*) as count')
			->from(DC::p('plugins'), 'p')
			->where('identifier = :identifier')
			->andWhere('slug = :slug')
			->setParameters([':identifier' => $module, ':slug' => $slug])
			->execute()
			->fetch()['count'];

		// if the plugin isn't installed yet, we will run install.php and NOT enable.php
		if ( ! $count)
		{
			return static::install($module, $slug);
		}

		DC::qb()
			->update(DC::p('plugins'))
			->set('enabled', ':enabled')
			->where('identifier = :identifier')
			->andWhere('slug = :slug')
			->setParameters(['enabled' => true, ':identifier' => $module, ':slug' => $slug])
			->execute();

		static::clear_cache();
	}

	/**
	 * Disables plugin and runs plugin_disable()
	 */
	public static function disable($module, $slug)
	{
		$plugin = static::$loader->get($module, $slug);
		$dir = $plugin->getDir();

		if (file_exists($dir.'disable.php'))
		{
			\Fuel::load($dir.'disable.php');
		}

		DC::qb()
			->update(DC::p('plugins'))
			->set('enabled', ':enabled')
			->where('identifier = :identifier')
			->andWhere('slug = :slug')
			->setParameters([':enabled' => false, ':identifier' => $module, ':slug' => $slug])
			->execute();

		static::clear_cache();
	}

	public static function install($module, $slug)
	{
		$plugin = static::$loader->get($module, $slug);

		$plugin->install();

		DC::forge()->insert(DC::p('plugins'), ['identifier' => $module, 'slug' => $slug, 'enabled' => true]);

		static::clear_cache();

		// run the schema update
		$sm = \Foolz\Foolframe\Model\SchemaManager::forge(DC::forge(), DC::getPrefix().'plugin_');

		foreach (static::get_installed() as $enabled)
		{
			try
			{
				$plug = static::$loader->get($enabled['identifier'], $enabled['slug']);

				if ( ! $plug->isBootstrapped())
				{
					$plug->bootstrap();
				}

				\Foolz\Plugin\Hook::forge('Foolz\Foolframe\Model\Plugin::schemaUpdate.'.$plug->getConfig('name'))
					->setParam('schema', $sm->getCodedSchema())
					->execute();
			}
			catch (\OutOfBoundsException $e)
			{}
		}

		$sm->commit();

		static::clear_cache();
	}

	public static function uninstall($idenfitier, $slug)
	{
		$dir = static::get_plugin_dir($identifier, $slug);

		if (file_exists($dir.'uninstall.php'))
		{
			\Fuel::load($dir.'uninstall.php');
		}

		DC::qb()
			->delete(DC::p('plugins'))
			->where('identifier = :identifier')
			->andWhere('slug = :slug')
			->setParameters([':identifier' => $identifier, ':slug' => $slug])
			->execute();

		static::clear_cache();
	}

	public static function get_sidebar_elements($type)
	{
		if ( ! isset(static::$_admin_sidebars[$type]))
		{
			return array();
		}

		return static::$_admin_sidebars[$type];
	}

	public static function register_sidebar_element($type, $section, $array = null)
	{
		// the user can also send an array with the index inseted in $section
		if( ! is_null($array))
		{
			$array2 = array();
			$array2[$section] = $array;
			$array = $array2;
		}
		else
		{
			$array = $section;
		}

		static::$_admin_sidebars[$type][] = $array;

		\Foolz\Foolframe\Controller\Admin::add_sidebar_element($array);
	}

}