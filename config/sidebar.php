<?php

$sidebar = [];

$sidebar['account'] = [
	'name' => _i('Account'),
	'level' => 'user',
	'default' => 'profile',
	'content' => [
		'profile' => [
			'level' => 'user',
			'name' => _i('Profile'),
			'icon' => 'icon-user'
		],
		'change_email' => [
			'level' => 'user',
			'name' => _i('Change Email'),
			'icon' => 'icon-envelope'
		],
		'change_password' => [
			'level' => 'user',
			'name' => _i('Change Password'),
			'icon' => 'icon-lock'
		],
		'delete' => [
			'level' => 'user',
			'name' => _i('Delete Account'),
			'icon' => 'icon-remove-circle'
		]
	]
];

$sidebar['users'] = [
	'name' => _i('Users'),
	'level' => 'mod',
	'default' => 'manage',
	'content' => [
		'manage' => [
			'alt_highlight' => ['member'],
			'level' => 'mod',
			'name' => _i('Manage'),
			'icon' => 'icon-user'
		]
	]
];

$sidebar['preferences'] = [
	'name' => _i('Preferences'),
	'level' => 'admin',
	'default' => 'general',
	'content' => [
		'general' => [
			'level' => 'admin',
			'name' => _i('General'),
			'icon' => 'icon-picture'
		],
		'registration' => [
			'level' => 'admin',
			'name' => _i('Registration'),
			'icon' => 'icon-book'
		],
		'advertising' => [
			'level' => 'admin',
			'name' => _i('Advertising'),
			'icon' => 'icon-lock'
		]
	]
];

$sidebar['system'] = [
	'name' => _i('System'),
	'level' => 'admin',
	'default' => 'information',
	'content' => [
		'information' => [
			'level' => 'admin',
			'name' => _i('Information'),
			'icon' => 'icon-info-sign'
		]
	]
];

$sidebar['plugins'] = [
	'name' => _i('Plugins'),
	'level' => 'admin',
	'default' => 'manage',
	'content' => [
		'manage' => [
			'level' => 'admin',
			'name' => _i('Manage'),
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
			'name' => _i('Developer Community'),
			'icon' => 'icon-comment'
		],
		'http://www.foolz.us/' => [
			'level' => 'user',
			'name' => _i('Developer Site'),
			'icon' => 'icon-home'
		],
		'https://github.com/FoolCode/' => [
			'level' => 'user',
			'name' => _i('FoolCode GitHub'),
			'icon' => 'icon-qrcode'
		]
	]
];

return ['sidebar' => $sidebar];