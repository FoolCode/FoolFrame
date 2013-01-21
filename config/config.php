<?php

return array(
	/**
	 * Variables necessary for the installation module to work
	 */
	'install' => array(
		/**
		 * This must be turned to true so the application can run,
		 * otherwise only the install module will be available
		 */
		'installed' => false
	),

	/**
	 * Configurations that can be changed by the user
	 */
	'config' => array(
		'cookie_prefix' => ''
	),

	/**
	 * Information about the modules supported by FoolFrame
	 */
	'modules' => array(
		/**
		 * The installed modules
		 */
		'installed' => array(
			'ff' => 'foolz/foolframe'
		)
	)
);