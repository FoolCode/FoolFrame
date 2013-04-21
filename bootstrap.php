<?php

use Foolz\Config\Config;

// start up the caching system
$caching_config = Config::get('foolz/foolframe', 'cache', '');
switch ($caching_config['type'])
{
	case 'apc':
		$apc_config = \Foolz\Cache\Config::forgeApc();
		$apc_config->setFormat($caching_config['format']);
		$apc_config->setPrefix($caching_config['prefix']);
		$apc_config->setThrow(true);
		\Foolz\Cache\Cache::instantiate($apc_config);
		break;

	case 'memcached':
		$mem_config = \Foolz\Cache\Config::forgeMemcached();
		$mem_config->setFormat($caching_config['format']);
		$mem_config->setPrefix($caching_config['prefix']);
		$mem_config->setServers($caching_config['servers']);
		$mem_config->setThrow(true);
		\Foolz\Cache\Cache::instantiate($mem_config);
		break;

	case 'dummy':
		$dummy_config = \Foolz\Cache\Config::forgeDummy();
		$dummy_config->setFormat($caching_config['format']);
		$dummy_config->setPrefix($caching_config['prefix']);
		$dummy_config->setThrow(true);
		\Foolz\Cache\Cache::instantiate($dummy_config);
		break;
}

\Module::load('foolz/foolframe', VENDPATH.'foolz/foolframe/');

// TODO convert this into "use" references
class_alias('Foolz\\Foolframe\\Model\\DoctrineConnection', 'DoctrineConnection');
class_alias('Foolz\\Foolframe\\Model\\Notices', 'Notices');
class_alias('Foolz\\Foolframe\\Model\\Plugins', 'Plugins');
class_alias('Foolz\\Foolframe\\Model\\Preferences', 'Preferences');
class_alias('Foolz\\Foolframe\\Model\\SchemaManager', 'SchemaManager');
class_alias('Foolz\\Foolframe\\Model\\System', 'System');
class_alias('Foolz\\Foolframe\\Model\\User', 'User');
class_alias('Foolz\\Foolframe\\Model\\Users', 'Users');

// check if FoolFrame is installed and in case it's not, allow reaching install
if ( ! Config::get('foolz/foolframe', 'config', 'install.installed'))
{
	\Module::load('install', PKGPATH.'foolz/install/');
	require PKGPATH.'foolz/install/bootstrap.php';
}
else
{
	// load each FoolFrame module, bootstrap and config
	foreach(Config::get('foolz/foolframe', 'config', 'modules.installed') as $module)
	{
		// foolframe is already loaded
		if ($module !== 'foolz/foolframe')
		{
			\Module::load($module, VENDPATH.$module.'/');
		}

		// load the module routing
		foreach(Config::get($module, 'autoroutes') as $key => $item)
		{
			\Router::add($key, $item);
		}
	}

	// run the bootstrap for each module
	foreach(Config::get('foolz/foolframe', 'config', 'modules.installed') as $module)
	{
		if ($module !== 'foolz/foolframe')
		{
			require VENDPATH.$module.'/bootstrap.php';
		}
	}

	$available_langs = Config::get('foolz/foolframe', 'package', 'preferences.lang.available');
	$lang = \Cookie::get('language');

	if( ! $lang || ! array_key_exists($lang, $available_langs))
	{
		$lang = \Preferences::get('foolframe.lang.default');
	}

	$locale = $lang . '.utf8';
	putenv('LANG=' . $locale);
	putenv('LANGUAGE=' . $locale);
	if ($locale !== "tr_TR.utf8") // long standing PHP bug
	{
		setlocale(LC_ALL, $locale);
	}
	else // workaround to make turkish work
	{
		setlocale(LC_COLLATE, $locale);
		setlocale(LC_MONETARY, $locale);
		setlocale(LC_NUMERIC, $locale);
		setlocale(LC_TIME, $locale);
		setlocale(LC_MESSAGES, $locale);
		setlocale(LC_CTYPE, "sk_SK.utf8");
	}

	bindtextdomain($lang, DOCROOT . "assets/locale");
	bind_textdomain_codeset($lang, 'UTF-8');
	textdomain($lang);

	\Foolz\Foolframe\Model\Plugins::initialize();
}