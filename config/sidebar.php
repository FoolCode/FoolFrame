<?php

$sidebar = [];

$sidebar['account'] = [
	'name' => __('Account'),
	'level' => 'user',
	'default' => 'change_email',
	'content' => [
		'profile' => [
			'level' => 'user',
			'name' => __('Profile'),
			'icon' => 'icon-user'
		],
		'change_email' => [
			'level' => 'user',
			'name' => __('Change Email'),
			'icon' => 'icon-envelope'
		],
		'change_password' => [
			'level' => 'user',
			'name' => __('Change Password'),
			'icon' => 'icon-lock'
		],
		'delete' => [
			'level' => 'user',
			'name' => __('Delete Account'),
			'icon' => 'icon-remove-circle'
		]
	]
];

$sidebar['users'] = [
	'name' => __('Users'),
	'level' => 'mod',
	'default' => 'users',
	'content' => [
		'manage' => [
			'alt_highlight' => ['member'],
			'level' => 'mod',
			'name' => __('Manage'),
			'icon' => 'icon-user'
		]
	]
];

$sidebar['preferences'] = [
	'name' => __('Preferences'),
	'level' => 'admin',
	'default' => 'general',
	'content' => [
		'theme' => [
			'level' => 'admin',
			'name' => __('Theme'),
			'icon' => 'icon-picture'
		],
		'registration' => [
			'level' => 'admin',
			'name' => __('Registration'),
			'icon' => 'icon-book'
		],
		'advertising' => [
			'level' => 'admin',
			'name' => __('Advertising'),
			'icon' => 'icon-lock'
		]
	]
];

$sidebar['system'] = [
	'name' => __('System'),
	'level' => 'admin',
	'default' => 'system',
	'content' => [
		'information' => [
			'level' => 'admin',
			'name' => __('Information'),
			'icon' => 'icon-info-sign'
		],
		'preferences' => [
			'level' => 'admin',
			'name' => __('Preferences'),
			'icon' => 'icon-check'
		],
		'upgrade' => [
			'level' => 'admin',
			'name' => __('Upgrade') . ((\Preferences::get('ff.cron_autoupgrade_version') && version_compare(\Config::get('foolframe.main.version'),
				\Preferences::get('ff.cron_autoupgrade_version')) < 0) ? ' <span class='label label-success'>' . __('New') . '</span>'
					: ''),
			'icon' => 'icon-refresh'
		]
	]
];

$sidebar['plugins'] = [
	'name' => __('Plugins'),
	'level' => 'admin',
	'default' => 'manage',
	'content' => [
		'manage' => [
			'level' => 'admin',
			'name' => __('Manage'),
			'icon' => 'icon-gift'
		]
	]
];

$sidebar['meta'] = [
	'name' => 'Meta', // no gettext because meta must be meta
	'level' => 'member',
	'default' => 'http://ask.foolrulez.com',
	'content' => [
		'https://github.com/FoOlRulez/FoOlFuuka/issues' => [
			'level' => 'member',
			'name' => __('Bug tracker'),
			'icon' => 'icon-exclamation-sign'
		]
	]
];

return ['sidebar' => $sidebar];