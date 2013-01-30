<?php

$sidebar = [];

$sidebar['account'] = [
	'name' => __('Account'),
	'level' => 'user',
	'default' => 'profile',
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
	'default' => 'manage',
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
		'general' => [
			'level' => 'admin',
			'name' => __('General'),
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
	'default' => 'information',
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
			'name' => __('Upgrade'),
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
	'level' => 'user',
	'default' => 'http://www.foolz.us/',
	'content' => [
		'https://archive.foolz.us/dev/' => [
			'level' => 'user',
			'name' => __('Developer Community'),
			'icon' => 'icon-comment'
		],
		'http://www.foolz.us/' => [
			'level' => 'user',
			'name' => __('Developer Site'),
			'icon' => 'icon-home'
		],
		'https://github.com/FoolCode/' => [
			'level' => 'user',
			'name' => __('FoolCode GitHub'),
			'icon' => 'icon-qrcode'
		]
	]
];

return ['sidebar' => $sidebar];