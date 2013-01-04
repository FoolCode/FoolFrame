<?php

use \Foolz\Foolframe\Model\DoctrineConnection as DC;

\Foolz\Plugin\Event::forge('Foolz\Plugin\Plugin::execute.foolz/foolframe-plugin-articles')
	->setCall(function($result) {
		\Autoloader::add_classes(array(
			'Foolz\Foolframe\Plugins\Articles\Model\Articles' => __DIR__.'/classes/model/articles.php',
			'Foolz\Foolframe\Controller\Admin\Articles' => __DIR__.'/classes/controller/admin.php',
			'Foolframe\\Plugins\\Articles\\Controller_Plugin_Ff_Articles_Chan' => __DIR__.'/classes/controller/chan.php'
		));

		// don't add the admin panels if the user is not an admin
		if (\Auth::has_access('maccess.admin'))
		{
			\Router::add('admin/articles', 'foolz/foolframe/admin/articles/manage');
			\Router::add('admin/articles/(:any)', 'foolz/foolframe/admin/articles/$1');

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
		}/*

		\Router::add('_/articles/(:any)', 'plugin/ff/articles/chan/articles/$1', true);

		\Foolz\Plugin\Event::forge('ff.themes.generic_top_nav_buttons')
			->setCall('Foolz\Foolframe\Plugins\Articles\Model\Articles::get_top')
			->setPriority(3);

		\Foolz\Plugin\Event::forge('ff.themes.generic_bottom_nav_buttons')
			->setCall('Foolz\Foolframe\Plugins\Articles\Model\Articles::get_bottom')
			->setPriority(3);

		\Foolz\Plugin\Event::forge('ff.themes.generic.index_nav_elements')
			->setCall('Foolz\Foolframe\Plugins\Articles\Model\Articles::get_index')
			->setPriority(3);
		*/
	});

\Foolz\Plugin\Event::forge('Foolz\Plugin\Plugin::install.foolz/foolframe-plugin-articles')
	->setCall(function($result) {

	\Foolz\Plugin\Event::forge('Foolz\Foolframe\Model\Plugin::schemaUpdate')
		->setCall(function($result) {
			/* @var $schema \Doctrine\DBAL\Schema\Schema */
			/* @var $table \Doctrine\DBAL\Schema\Table */
			$schema = $result->getParam('schema');
			$table = $schema->createTable(DC::p('plugin_ff_articles'));
			if (DC::forge()->getDriver()->getName() == 'pdo_mysql')
			{
				$table->addOption('charset', 'utf8mb4');
				$table->addOption('collate', 'utf8mb4_unicode_ci');
			}
			$table->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
			$table->addColumn('slug', 'string', ['length' => 128]);
			$table->addColumn('title', 'string', ['length' => 256]);
			$table->addColumn('url', 'string', ['length' => 256]);
			$table->addColumn('article', 'text', ['length' => 65532]);
			$table->addColumn('active', 'smallint', ['unsigned' => true, 'default' => 0]);
			$table->addColumn('top', 'smallint', ['unsigned' => true, 'default' => 0]);
			$table->addColumn('bottom', 'smallint', ['unsigned' => true, 'default' => 0]);
			$table->setPrimaryKey(['id']);
			$table->addIndex(['slug'], 'slug_index');
		});

	});

\Foolz\Plugin\Event::forge('foolz\plugin\plugin.uninstall.foolz/articles')
	->setCall(function($result) {
		\DB::drop_database('plugin_ff-articles');
	});