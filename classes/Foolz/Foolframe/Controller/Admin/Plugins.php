<?php

namespace Foolz\Foolframe\Controller\Admin;

use Foolz\Foolframe\Model\PluginException;
use Foolz\Foolframe\Model\Plugins as PluginsModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Plugins extends \Foolz\Foolframe\Controller\Admin
{
	public function before(Request $request)
	{
		if( ! \Auth::has_access('maccess.admin'))
		{
			Response::redirect('admin');
		}

		parent::before($request);

		// set controller title
		$this->param_manager->setParam('controller_title', __('Plugins'));
	}

	function action_manage()
	{
		$data = [];
		$data['plugins'] = PluginsModel::getAll();
		$this->param_manager->setParam('method_title', __('Manage'));
		$this->builder->createPartial('body', 'plugins/manage')
			->getParamManager()->setParams($data);
		return new Response($this->builder->build());
	}

	function action_action()
	{
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

		$plugin = PluginsModel::getPlugin($identifier, $slug);

		if ( ! $plugin)
		{
			throw new \HttpNotFoundException;
		}

		switch ($action)
		{
			case 'enable':
				try
				{
					PluginsModel::enable($identifier, $slug);
				}
				catch (PluginException $e)
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
					PluginsModel::disable($identifier, $slug);
				}
				catch (PluginException $e)
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
					PluginsModel::remove($identifier, $slug);
				}
				catch (PluginException $e)
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