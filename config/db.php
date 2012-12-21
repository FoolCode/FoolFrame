<?php

return array(
	'active' => 'default',

	/**
	 * Base config, just need to set the DSN, username and password in env. config.
	 */
	'default' => array(
		'driver' => 'pdo_mysql',
		'host' => 'localhost',
		'port' => '3306',
		'dbname' => '',
		'user' => '',
		'password' => '',
		'persistent' => false,
		'prefix' => '',
		'charset' => 'utf8mb4'
	),

);
