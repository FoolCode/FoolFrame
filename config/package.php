<?php

return array(

	/**
	 * FoolFrame is the general structure built on FuelPHP
	 */
	'main' => array(

		/**
		 * Version number for upgrading
		 */
		'version' => '0.1-dev-0',

		/**
		 * Display name
		 */
		'name' => 'FoolFrame',

		/**
		 * The two letter identifier
		 */
		'identifier' => 'ff',

		/**
		 * The name that can be used in classes names
		 */
		'class_name' => 'Foolframe',

		/**
		 *  URL to download a newer version
		 */
		'git_tags_url' => 'https://api.github.com/repos/foolrulez/foolfuuka/tags',

		/**
		 * URL to fetch the changelog
		 */
		'git_changelog_url' => 'https://raw.github.com/foolrulez/FoOlFuuka/master/CHANGELOG.md',

	),

	/**
	 * Variables necessary for the installation module to work
	 */
	'install' => array(

		/**
		 * This must be turned to true so the application can run,
		 * otherwise only the install module will be available
		 */
		'installed' => false,

		/**
		 * Requirements to install FoolFrame
		 */
		'requirements' => array(
			/**
			* Minimal PHP requirement
			*/
			'min_php_version' => '5.3.0',

			/**
			 * Minimal MySQL requirement
			 */
			'min_mysql_version' => '5.5.0'
		)
	),

	/**
	 * Locations of the data out of the module folder
	 */
	'directories' => array(
		'themes' => 'public/themes/',
		'plugins' => 'public/plugins/'
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
	),

	/**
	 * Preferences defaults
	 */
	'preferences' => array(

		'gen' => array(
			'website_title' => 'FoolFrame',
			'index_title' => 'FoolFrame',
		),

		'lang' => array(
			'default' => 'en_EN',
			'available' => array(
				'en_EN' => 'English',
				'fr_FR' => 'French',
				'it_IT' => 'Italian',
				'pt_PT' => 'Portuguese',
			)
		)
	),

);