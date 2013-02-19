<?php

namespace Foolz\Foolframe\Model;

use \Foolz\Foolframe\Model\DoctrineConnection as DC;
use \Foolz\Cache\Cache;
use \Foolz\Plugin\Loader;

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

		foreach (static::getEnabled() as $enabled)
		{
			try
			{
				static::$loader->get($enabled['identifier'], $enabled['slug'])->execute();
				static::$loader->get($enabled['identifier'], $enabled['slug'])->enabled = true;
			}
			catch (\OutOfBoundsException $e)
			{

			}
		}
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
		try
		{
			$result = Cache::item('foolframe.model.plugins.get_enabled.query')->get();
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

	public static function getPlugin($module, $slug)
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

		static::clearCache();
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

		static::clearCache();
	}

	public static function install($module, $slug)
	{
		$plugin = static::$loader->get($module, $slug);
		$plugin->install();

		DC::forge()->insert(DC::p('plugins'), ['identifier' => $module, 'slug' => $slug, 'enabled' => true]);

		static::clearCache();

		// run the schema update
		$sm = \Foolz\Foolframe\Model\SchemaManager::forge(DC::forge(), DC::getPrefix().'plugin_');

		foreach (static::getInstalled() as $enabled)
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
			{

			}
		}

		$sm->commit();

		static::clearCache();
	}

	public static function uninstall($identifier, $slug)
	{
		$dir = static::getPluginDir($identifier, $slug);

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

		static::clearCache();
	}

	public static function getSidebarElements($type)
	{
		if ( ! isset(static::$_admin_sidebars[$type]))
		{
			return [];
		}

		return static::$_admin_sidebars[$type];
	}

	public static function registerSidebarElement($type, $section, $array = null)
	{
		// the user can also send an array with the index inseted in $section
		if( ! is_null($array))
		{
			$array2 = [];
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