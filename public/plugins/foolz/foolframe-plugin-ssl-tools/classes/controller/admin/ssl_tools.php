<?php

namespace Foolz\Foolframe\Controller\Admin\Plugins;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SslTools extends \Foolz\Foolframe\Controller\Admin
{
	public function before(Request $request)
	{
		if ( ! \Auth::has_access('maccess.admin'))
		{
			\Response::redirect('admin');
		}

		parent::before($request);

		$this->param_manager->setParam('controller_title', _i('Plugins'));
	}

	public function action_manage()
	{
		$this->param_manager->setParam('method_title', [_i('FoolFrame'), _i('SSL Tools'), 'SSL']);

		$form = array();

		$form['open'] = array(
			'type' => 'open'
		);

		$form['foolframe.plugins.ssl_tools.available'] = array(
			'type' => 'checkbox',
			'preferences' => true,
			'help' => _i('Does the server have SSL available (does your site support https:// protocol)?'),
			'sub' => array(
				'foolframe.plugins.ssl_tools.force_for_logged' => array(
					'type' => 'checkbox',
					'preferences' => true,
					'help' => _i('Redirect the logged in users to the SSL version of the site')
				),
				'foolframe.plugins.ssl_tools.force_everyone' => array(
					'type' => 'checkbox',
					'preferences' => true,
					'help' => _i('Redirect every user to the SSL version of the site')
				),
				'foolframe.plugins.ssl_tools.sticky' => array(
					'type' => 'checkbox',
					'preferences' => true,
					'help' => _i('Set a cookie for users that browsed the site with https:// so they get redirected to the https:// version of the site')
				),
				'foolframe.plugins.ssl_tools.enable_top_link' => array(
					'type' => 'checkbox',
					'preferences' => true,
					'help' => _i('Show a link to SSL in the header if the user is browsing in http://')
				),
				'foolframe.plugins.ssl_tools.enable_bottom_link' => array(
					'type' => 'checkbox',
					'preferences' => true,
					'help' => _i('Show a link to SSL in the footer if the user is browsing in http://')
				),
			)
		);

		$form['separator'] = array(
			'type' => 'separator'
		);

		$form['submit'] = array(
			'type' => 'submit',
			'value' => _i('Submit'),
			'class' => 'btn btn-primary'
		);

		$form['close'] = array(
			'type' => 'close'
		);

		\Preferences::submit_auto($form);

		$data['form'] = $form;

		// create a form
		$this->builder->createPartial('body', 'form_creator')
			->getParamManager()->setParams($data);

		return new Response($this->builder->build());
	}
}