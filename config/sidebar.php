<?php

$sidebar = [];

$sidebar["account"] = array(
	"name" => __("Account"),
	"level" => "user",
	"default" => "change_email",
	"content" => array(
		"profile" => array("level" => "user", "name" => __("Profile"), "icon" => 'icon-user'),
		"change_email" => array("level" => "user", "name" => __("Change Email"), "icon" => 'icon-envelope'),
		"change_password" => array("level" => "user", "name" => __("Change Password"), "icon" => 'icon-lock'),
		"delete" => array("level" => "user", "name" => __("Delete Account"), "icon" => 'icon-remove-circle'),
	)
);

$sidebar["users"] = array(
	"name" => __("Users"),
	"level" => "mod",
	"default" => "users",
	"content" => array(
		"manage" => array("alt_highlight" => array("member"),
			"level" => "mod", "name" => __("Manage"), "icon" => 'icon-user'),
	)
);

$sidebar["preferences"] = array(
	"name" => __("Preferences"),
	"level" => "admin",
	"default" => "general",
	"content" => array(
		"theme" => array("level" => "admin", "name" => __("Theme"), "icon" => 'icon-picture'),
		"registration" => array("level" => "admin", "name" => __("Registration"), "icon" => 'icon-book'),
		"advertising" => array("level" => "admin", "name" => __("Advertising"), "icon" => 'icon-lock'),
	)
);

$sidebar["system"] = array(
	"name" => __("System"),
	"level" => "admin",
	"default" => "system",
	"content" => array(
		"information" => array("level" => "admin", "name" => __("Information"), "icon" => 'icon-info-sign'),
		"preferences" => array("level" => "admin", "name" => __("Preferences"), "icon" => 'icon-check'),
		"upgrade" => array("level" => "admin", "name" => __("Upgrade") . ((\Preferences::get('ff.cron_autoupgrade_version') && version_compare(\Config::get('foolframe.main.version'),
				\Preferences::get('ff.cron_autoupgrade_version')) < 0) ? ' <span class="label label-success">' . __('New') . '</span>'
					: ''), "icon" => 'icon-refresh'),
	)
);

$sidebar["plugins"] = array(
	"name" => __("Plugins"),
	"level" => "admin",
	"default" => "manage",
	"content" => array(
		"manage" => array("level" => "admin", "name" => __("Manage"), "icon" => 'icon-gift'),
	)
);

$sidebar["meta"] = array(
	"name" => "Meta", // no gettext because meta must be meta
	"level" => "member",
	"default" => "http://ask.foolrulez.com",
	"content" => array(
		"https://github.com/FoOlRulez/FoOlFuuka/issues" => array("level" => "member", "name" => __("Bug tracker"), "icon" => 'icon-exclamation-sign'),
	)
);

return ['sidebar' => $sidebar];