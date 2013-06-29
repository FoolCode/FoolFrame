<?php

namespace Foolz\Foolframe\Controller\Admin;

use Foolz\Foolframe\Model\PluginException;
use Foolz\Foolframe\Model\Plugins as PluginsModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
		$this->param_manager->setParam('controller_title', _i('Plugins'));
	}

	function action_manage()
	{
		$data = [];
		$data['plugins'] = PluginsModel::getAll();

		$this->param_manager->setParam('method_title', _i('Manage'));
		$this->builder->createPartial('body', 'plugins/manage')
			->getParamManager()->setParams($data);

		return new Response($this->builder->build());
	}

	function action_action()
	{
		if (\Input::post() && ! \Security::check_token())
		{
			\Notices::setFlash('warning', _i('The security token wasn\'t found. Try resubmitting.'));
			\Response::redirect('admin/plugins/manage');
		}

		if ( ! $slug = \Input::post('name'))
		{
			throw new NotFoundHttpException;
		}

		if ( ! \Input::post('action') || !in_array(\Input::post('action'), array('enable', 'disable', 'remove')))
		{
			throw new NotFoundHttpException;
		}

		$action = \Input::post('action');

		$plugin = PluginsModel::getPlugin($slug);

		if ( ! $plugin)
		{
			throw new NotFoundHttpException;
		}

		switch ($action)
		{
			case 'enable':
				try
				{
					PluginsModel::enable($slug);
				}
				catch (PluginException $e)
				{
					\Notices::setFlash('error', _i('The plugin %s couldn\'t be enabled.', $plugin->getJsonConfig('extra.name')));
					break;
				}

				\Notices::setFlash('success', _i('The %s plugin is now enabled.', $plugin->getJsonConfig('extra.name')));

				break;

			case 'disable':
				try
				{
					PluginsModel::disable($slug);
				}
				catch (PluginException $e)
				{
					\Notices::setFlash('error', _i('The %s plugin couldn\'t be enabled.', $plugin->getJsonConfig('extra.name')));
					break;
				}

				\Notices::setFlash('success', _i('The %s plugin is now disabled.', $plugin->getJsonConfig('extra.name')));
				break;

			case 'upgrade':
				break;

			case 'remove':
				try
				{
					PluginsModel::remove($slug);
				}
				catch (PluginException $e)
				{
					\Notices::setFlash('error', _i('The :slug plugin couldn\'t be removed.', $plugin->getJsonConfig('extra.name')));
					break;
				}
				\Notices::setFlash('success', _i('The :slug plugin was removed.', $plugin->getJsonConfig('extra.name')));
				break;
		}

		\Response::redirect('admin/plugins/manage');
	}
}