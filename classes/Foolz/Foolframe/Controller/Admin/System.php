<?php

namespace Foolz\Foolframe\Controller\Admin;

use \Foolz\Autoupgrade\Upgrade;

class System extends \Foolz\Foolframe\Controller\Admin
{
	public function before()
	{
		parent::before();

		if( ! \Auth::has_access('maccess.admin'))
		{
			Response::redirect('admin');
		}

		// set controller title
		$this->_views['controller_title'] = __('System');
	}

	public function action_upgrade_modules()
	{
		$modules = \Foolz\Config\Config::get('foolz/foolframe', 'config', 'modules.installed');

		foreach ($modules as $module)
		{
			// module
			$upgrade_module = Upgrade::forge(APPPATH.'modules/'.$module);

			if ($upgrade_module->check())
			{
				$mods[$module]['module'] = $upgrade_module;
			}

			// public, doesn't necessarily exist
			if (is_dir(DOCROOT.$module))
			{
				$upgrade_public = Upgrade::forge(DOCROOT.$module);
				if ($upgrade_module->check())
				{
					$mods[$module]['public'] = $upgrade_public;
				}
			}
		}

		$foolframe = Upgrade::forge(DOCROOT.'..');
		$foolframe->check();

		$this->_views['method_title'] = __('Manage');
		$this->_views["main_content_view"] = \View::forge('admin/plugins/manage', $data);
		return \Response::forge(\View::forge('admin/default', $this->_views));

		/*
		if (\Input::post() && ! \Security::check_token())
		{
			\Notices::set('warning', __('The security token wasn\'t found. Try resubmitting.'));
		}
		else if (Input::post())
		{
			// run upgrades for every module
			foreach ($mods as $mod)
			{
				$mod->run();
			}
		}*/
	}
}