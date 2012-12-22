<?php
if (!defined('DOCROOT'))
	exit('No direct script access allowed');

\Foolz\Plugin\Event::forge('foolz\plugin\plugin.execute.foolz/articles')
	->setCall(function($result) {
		\Autoloader::add_classes(array(
			'Foolframe\\Plugins\\Articles\\Articles' => __DIR__.'/classes/model/articles.php',
			'Foolframe\\Plugins\\Articles\\Controller_Plugin_Ff_Articles_Admin_Articles'
				=> __DIR__.'/classes/controller/admin.php',
			'Foolframe\\Plugins\\Articles\\Controller_Plugin_Ff_Articles_Chan'
				=> __DIR__.'/classes/controller/chan.php'
		));

		// don't add the admin panels if the user is not an admin
		if (\Auth::has_access('maccess.admin'))
		{
			\Router::add('admin/articles', 'plugin/ff/articles/admin/articles/manage');
			\Router::add('admin/articles/(:any)', 'plugin/ff/articles/admin/articles/$1');

			\Plugins::register_sidebar_element('admin', 'articles', array(
					"name" => __("Articles"),
					"default" => "manage",
					"position" => array(
						"beforeafter" => "after",
						"element" => "posts"
					),
					"level" => "admin",
					"content" => array(
						"manage" => array("level" => "admin", "name" => __("Articles"), "icon" => 'icon-font'),
					)
				)
			);
		}

		\Router::add('_/articles/(:any)', 'plugin/ff/articles/chan/articles/$1', true);

		\Foolz\Plugin\Event::forge('ff.themes.generic_top_nav_buttons')
			->setCall('Foolframe\\Plugins\\Articles\\Articles::get_top')
			->setPriority(3);

		\Foolz\Plugin\Event::forge('ff.themes.generic_bottom_nav_buttons')
			->setCall('Foolframe\\Plugins\\Articles\\Articles::get_bottom')
			->setPriority(3);

		\Foolz\Plugin\Event::forge('ff.themes.generic.index_nav_elements')
			->setCall('Foolframe\\Plugins\\Articles\\Articles::get_index')
			->setPriority(3);
	});

\Foolz\Plugin\Event::forge('foolz\plugin\plugin.install.foolz/articles')
	->setCall(function($result) {
		$charset = \Config::get('db.default.charset');

		if (!\DBUtil::table_exists('plugin_ff-articles'))
		{
			\DBUtil::create_table('plugin_ff-articles', array(
				'id' => array('type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true),
				'slug' => array('type' => 'varchar', 'constraint' => 128),
				'title' => array('type' => 'varchar', 'constraint' => 256),
				'url' => array('type' => 'text'),
				'article' => array('type' => 'text'),
				'active' => array('type' => 'smallint', 'constraint' => 2),
				'top' => array('type' => 'smallint', 'constraint' => 2),
				'bottom' => array('type' => 'smallint', 'constraint' => 2),
				'edited' => array('type' => 'timestamp', 'default' => \DB::expr('CURRENT_TIMESTAMP'), 'on_update' => \DB::expr('CURRENT_TIMESTAMP')),
			), array('id'), true, 'innodb', $charset.'_general_ci');

			\DBUtil::create_index('plugin_ff-articles', 'slug', 'slug_index');
		}
	});

\Foolz\Plugin\Event::forge('foolz\plugin\plugin.uninstall.foolz/articles')
	->setCall(function($result) {
		\DB::drop_database('plugin_ff-articles');
	});