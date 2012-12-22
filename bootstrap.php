<?php

\Module::load('foolz/foolframe', VENDPATH.'foolz/foolframe/');

// TODO convert this into "use" references
class_alias('Foolz\\Foolframe\\Model\\Preferences', 'Preferences');

// check if FoolFrame is installed and in case it's not, allow reaching install
if ( ! \Foolz\Config\Config::get('foolz/foolframe', 'package', 'install.installed'))
{
	\Module::load('install', PKGPATH.'foolz/install/');
	require PKGPATH.'foolz/install/bootstrap.php';
}
else
{
	// load each FoolFrame module, bootstrap and config
	foreach(\Foolz\Config\Config::get('foolz/foolframe', 'package', 'modules.installed') as $module)
	{
		// foolframe is already loaded
		if ($module !== 'foolz/foolframe')
		{
			\Module::load($module, PKGPATH.$module.'/');
		}

		// load the module routing
		foreach(\Foolz\Config\Config::get($module, 'autoroutes') as $key => $item)
		{
			\Router::add($key, $item);
		}
	}

	// run the bootstrap for each module
	foreach(\Foolz\Config\Config::get('foolz/foolframe', 'package', 'modules.installed') as $module)
	{
		if ($module !== 'foolz/foolframe')
		{
			require PKGPATH.$module.'/bootstrap.php';
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

	\Plugins::initialize();
}