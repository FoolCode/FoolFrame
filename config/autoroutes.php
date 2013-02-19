<?php

return [
	'admin' => 'foolz/foolframe/admin/index',
	'admin/account' => 'foolz/foolframe/admin/account/login',
	'admin/account/(:any)' => 'foolz/foolframe/admin/account/$1',
	'admin/plugins' => 'foolz/foolframe/admin/plugins/manage',
	'admin/plugins/(:any)' => 'foolz/foolframe/admin/plugins/$1',
	'admin/preferences' => 'foolz/foolframe/admin/preferences/general',
	'admin/preferences/(:any)' => 'foolz/foolframe/admin/preferences/$1',
	'admin/system' => 'foolz/foolframe/admin/system/information',
	'admin/system/(:any)' => 'foolz/foolframe/admin/system/$1',
	'admin/users' => 'foolz/foolframe/admin/users/manage',
	'admin/users/(:any)' => 'foolz/foolframe/admin/users/$1'
];