<?php

namespace Foolz\Foolframe\Controller\Admin;

class Plugins extends \Foolz\Foolframe\Controller\Admin
{
	public function before()
	{
		if( ! \Auth::has_access('maccess.admin'))
		{
			Response::redirect('admin');
		}

		parent::before();

		// set controller title
		$this->_views['controller_title'] = __("Plugins");
	}

	function action_manage()
	{
		$data = [];
		$data['plugins'] = \Plugins::getAll();
		$this->_views['method_title'] = __('Manage');
		$this->_views["main_content_view"] = \View::forge('foolz/foolframe::admin/plugins/manage', $data);
		return \Response::forge(\View::forge('foolz/foolframe::admin/default', $this->_views));
	}


	function action_action($identifier, $vendor, $slug)
	{
		$slug = $vendor.'/'.$slug;

		if (\Input::post() && ! \Security::check_token())
		{
			\Notices::setFlash('warning', __('The security token wasn\'t found. Try resubmitting.'));
			\Response::redirect('admin/plugins/manage');
		}

		if ( ! $identifier = \Input::post('module'))
		{
			throw new \HttpNotFoundException;
		}

		if ( ! $slug = \Input::post('name'))
		{
			throw new \HttpNotFoundException;
		}

		if ( ! \Input::post('action') || !in_array(\Input::post('action'), array('enable', 'disable', 'remove')))
		{
			throw new \HttpNotFoundException;
		}

		$action = \Input::post('action');

		$plugin = \Plugins::getPlugin($identifier, $slug);

		if ( ! $plugin)
		{
			throw new \HttpNotFoundException;
		}

		switch ($action)
		{
			case 'enable':
				try
				{
					\Plugins::enable($identifier, $slug);
				}
				catch (\Plugins\PluginException $e)
				{
					\Notices::setFlash('error', \Str::tr(__('The plugin :slug couldn\'t be enabled.'),
						array('slug' => $plugin->getJsonConfig('extra.name'))));
					break;
				}

				\Notices::setFlash('success',
					\Str::tr(__('The :slug plugin is now enabled.'), array('slug' => $plugin->getJsonConfig('extra.name'))));

				break;

			case 'disable':
				try
				{
					\Plugins::disable($identifier, $slug);
				}
				catch (\Plugins\PluginException $e)
				{
					\Notices::setFlash('error', \Str::tr(__('The :slug plugin couldn\'t be enabled.'),
						array('slug' => $plugin->getJsonConfig('extra.name'))));
					break;
				}

				\Notices::setFlash('success',
					\Str::tr(__('The :slug plugin is now disabled.'), array('slug' => $plugin->getJsonConfig('extra.name'))));
				break;

			case 'upgrade':
				break;

			case 'remove':
				try
				{
					\Plugin::remove($identifier, $slug);
				}
				catch (\Plugins\PluginException $e)
				{
					\Notices::setFlash('error',
						\Str::tr(__('The :slug plugin couldn\'t be removed.'),
							array('slug' => $plugin->getJsonConfig('extra.name'))));
					break;
				}
				\Notices::setFlash('success',
					\Str::tr(__('The :slug plugin was removed.'), array('slug' => $plugin->getJsonConfig('extra.name'))));
				break;
		}

		\Response::redirect('admin/plugins/manage');
	}
}