<?php

// start up the default caching system
$apc_config = \Foolz\Cache\Config::forgeApc();
$apc_config->setFormat('smart_json');
$apc_config->setThrow(true);
\Foolz\Cache\Cache::instantiate($apc_config);

\Module::load('foolz/foolframe', VENDPATH.'foolz/foolframe/');

// TODO convert this into "use" references
class_alias('Foolz\\Foolframe\\Model\\DoctrineConnection', 'DoctrineConnection');
class_alias('Foolz\\Foolframe\\Model\\Plugins', 'Plugins');
class_alias('Foolz\\Foolframe\\Model\\Preferences', 'Preferences');
class_alias('Foolz\\Foolframe\\Model\\SchemaManager', 'SchemaManager');
class_alias('Foolz\\Foolframe\\Model\\Notices', 'Notices');
class_alias('Foolz\\Foolframe\\Model\\User', 'User');
class_alias('Foolz\\Foolframe\\Model\\Users', 'Users');

// check if FoolFrame is installed and in case it's not, allow reaching install
if ( ! \Foolz\Config\Config::get('foolz/foolframe', 'config', 'install.installed'))
{
	\Module::load('install', PKGPATH.'foolz/install/');
	require PKGPATH.'foolz/install/bootstrap.php';
}
else
{
	// load each FoolFrame module, bootstrap and config
	foreach(\Foolz\Config\Config::get('foolz/foolframe', 'config', 'modules.installed') as $module)
	{
		// foolframe is already loaded
		if ($module !== 'foolz/foolframe')
		{
			\Module::load($module, VENDPATH.$module.'/');
		}

		// load the module routing
		foreach(\Foolz\Config\Config::get($module, 'autoroutes') as $key => $item)
		{
			\Router::add($key, $item);
		}
	}

	// run the bootstrap for each module
	foreach(\Foolz\Config\Config::get('foolz/foolframe', 'config', 'modules.installed') as $module)
	{
		if ($module !== 'foolz/foolframe')
		{
			require VENDPATH.$module.'/bootstrap.php';
		}
	}

	$available_langs = \Config::get('foolframe.preferences.lang.available');
	$lang = \Cookie::get('language');

	if( ! $lang || ! array_key_exists($lang, $available_langs))
	{
		$lang = \Preferences::get('ff.lang.default');
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