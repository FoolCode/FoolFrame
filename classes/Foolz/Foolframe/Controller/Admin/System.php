<?php

namespace Foolz\Foolframe\Controller\Admin;

use \Foolz\Autoupgrade\Upgrade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class System extends \Foolz\Foolframe\Controller\Admin
{
	public function before(Request $request)
	{
		parent::before($request);

		if( ! \Auth::has_access('maccess.admin'))
		{
			Response::redirect('admin');
		}

		// set controller title
		$this->param_manager->setParam('controller_title', __('System'));
	}

	public function action_information()
	{
		$data = ['info' => \System::environment()];

		$this->param_manager->setParam('method_title', __('Information'));
		$this->builder->createPartial('body', 'system/information')
			->getParamManager()->setParams($data);

		return new Response($this->builder->build());
	}
}