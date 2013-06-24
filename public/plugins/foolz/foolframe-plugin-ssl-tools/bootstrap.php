<?php

\Foolz\Plugin\Event::forge('Foolz\Plugin\Plugin::execute.foolz/foolframe-plugin-ssl-tools')
	->setCall(function($result) {

		// this plugin works with indexes that don't exist in CLI
		if (PHP_SAPI === 'cli')
		{
			return false;
		}

		\Autoloader::add_classes(array(
			'Foolz\Foolframe\Plugins\SslTools\Model\SslTools' => __DIR__.'/classes/model/ssl_tools.php',
			'Foolz\Foolframe\Controller\Admin\Plugins\SslTools' => __DIR__.'/classes/controller/admin/ssl_tools.php'
		));

		// don't add the admin panels if the user is not an admin
		if (\Auth::has_access('maccess.admin'))
		{
			$result->getParam('framework')->getRouteCollection()->add(
				'foolframe.plugin.ssl_tools.admin', new \Symfony\Component\Routing\Route(
					'/admin/plugins/ssl_tools/{_suffix}',
					[
						'_suffix' => 'manage',
						'_controller' => '\Foolz\Foolframe\Controller\Admin\Plugins\SslTools::manage'
					],
					[
						'_suffix' => '.*'
					]
				)
			);

			\Plugins::registerSidebarElement('admin', 'plugins', array(
				'content' => array('ssl_tools/manage' => array('level' => 'admin', 'name' => _i('SSL Tools'), 'icon' => 'icon-lock'))
			));
		}

		// we can just run base checks now
		\Foolz\Foolframe\Plugins\SslTools\Model\SslTools::check();

		\Foolz\Plugin\Event::forge('foolframe.themes.generic_top_nav_buttons')
			->setCall('\Foolz\Foolframe\Plugins\SslTools\Model\SslTools::nav_top')
			->setPriority(4);

		\Foolz\Plugin\Event::forge('foolframe.themes.generic_bottom_nav_buttons')
			->setCall('\Foolz\Foolframe\Plugins\SslTools\Model\SslTools::nav_bottom')
			->setPriority(4);
	});