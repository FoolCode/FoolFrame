<?php

if (!defined('DOCROOT'))
	exit('No direct script access allowed');

\Foolz\Plugin\Event::forge('foolz\plugin\plugin.execute.foolz/ssl_tools')
	->setCall(function($result) {
		// this plugin works with indexes that don't exist in CLI
		if (PHP_SAPI === 'cli')
		{
			return false;
		}

		\Autoloader::add_classes(array(
			'Foolframe\\Plugins\\Ssl_Tools\\Ssl_Tools' => __DIR__.'/classes/model/ssl_tools.php',
			'Foolframe\\Plugins\\Ssl_Tools\\Controller_Plugin_Ff_Ssl_Tools_Admin_Ssl_Tools' => __DIR__.'/classes/controller/admin/ssl_tools.php'
		));

		// don't add the admin panels if the user is not an admin
		if (\Auth::has_access('maccess.admin'))
		{
			\Router::add('admin/plugins/ssl_tools', 'plugin/ff/ssl_tools/admin/ssl_tools/manage');

			\Plugins::register_sidebar_element('admin', 'plugins', array(
				"content" => array("ssl_tools" => array("level" => "admin", "name" => __("SSL Tools"), "icon" => 'icon-lock'))
			));
		}

		// we can just run base checks now
		\Foolframe\Plugins\Ssl_Tools\Ssl_Tools::check();

		\Foolz\Plugin\Event::forge('ff.themes.generic_top_nav_buttons')
			->setCall('\\Foolframe\\Plugins\\Ssl_Tools\\Ssl_Tools::nav_top')
			->setPriority(4);

		\Foolz\Plugin\Event::forge('ff.themes.generic_bottom_nav_buttons')
			->setCall('\\Foolframe\\Plugins\\Ssl_Tools\\Ssl_Tools::nav_bottom')
			->setPriority(4);
	});

