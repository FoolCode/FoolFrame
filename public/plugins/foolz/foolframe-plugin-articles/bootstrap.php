<?php

use \Foolz\Foolframe\Model\DoctrineConnection as DC;

\Foolz\Plugin\Event::forge('Foolz\Plugin\Plugin::execute.foolz/foolframe-plugin-articles')
	->setCall(function($result) {
		\Autoloader::add_classes(array(
			'Foolz\Foolframe\Plugins\Articles\Model\Articles' => __DIR__.'/classes/model/articles.php',
			'Foolz\Foolframe\Controller\Admin\Articles' => __DIR__.'/classes/controller/admin.php',
			'Foolz\Foolfuuka\Controller\Chan\Articles' => __DIR__.'/classes/controller/chan.php'
		));

		// don't add the admin panels if the user is not an admin
		if (\Auth::has_access('maccess.admin'))
		{
			\Router::add('admin/articles', 'foolz/foolframe/admin/articles/manage');
			\Router::add('admin/articles/(:any)', 'foolz/foolframe/admin/articles/$1');

			\Plugins::registerSidebarElement('admin', 'articles', array(
					'name' => __('Articles'),
					'default' => 'manage',
					'position' => array(
						'beforeafter' => 'before',
						'element' => 'account'
					),
					'level' => 'admin',
					'content' => array(
						'manage' => array('level' => 'admin', 'name' => __('Articles'), 'icon' => 'icon-font'),
					)
				)
			);
		}

		\Router::add('_/articles/(:any)', 'foolz/foolfuuka/chan/articles/articles/$1');

		\Foolz\Plugin\Event::forge('Fuel\Core\Router::parse_match.intercept')
			->setCall(function($result)
			{
				if ($result->getParam('controller') === 'Foolz\Foolfuuka\Controller\Chan')
				{
					if ($result->getParam('action') === 'articles')
					{
						$result->setParam('controller', 'Foolz\Foolfuuka\Controller\Chan\Articles');
						$result->set(true);
					}
				}
			})->setPriority(4);

		\Foolz\Plugin\Event::forge('foolframe.themes.generic_top_nav_buttons')
			->setCall('Foolz\Foolframe\Plugins\Articles\Model\Articles::getTop')
			->setPriority(3);

		\Foolz\Plugin\Event::forge('foolframe.themes.generic_bottom_nav_buttons')
			->setCall('Foolz\Foolframe\Plugins\Articles\Model\Articles::getBottom')
			->setPriority(3);

		\Foolz\Plugin\Event::forge('foolframe.themes.generic.index_nav_elements')
			->setCall('Foolz\Foolframe\Plugins\Articles\Model\Articles::getIndex')
			->setPriority(3);
	});

\Foolz\Plugin\Event::forge('Foolz\Foolframe\Model\Plugin::schemaUpdate.foolz/foolframe-plugin-articles')
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