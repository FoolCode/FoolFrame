<?php

namespace Foolz\Foolframe\Controller\Admin;

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

		$this->_views['controller_title'] = __('Plugins');
	}

	public function action_manage()
	{
		$this->_views['method_title'] = [__('FoolFrame'), __('SSL Tools'), 'SSL'];

		$form = array();

		$form['open'] = array(
			'type' => 'open'
		);

		$form['foolframe.plugins.ssl_tools.available'] = array(
			'type' => 'checkbox',
			'preferences' => true,
			'help' => __('Does the server have SSL available (does your site support https:// protocol)?'),
			'sub' => array(
				'foolframe.plugins.ssl_tools.force_for_logged' => array(
					'type' => 'checkbox',
					'preferences' => true,
					'help' => __('Redirect the logged in users to the SSL version of the site')
				),
				'foolframe.plugins.ssl_tools.force_everyone' => array(
					'type' => 'checkbox',
					'preferences' => true,
					'help' => __('Redirect every user to the SSL version of the site')
				),
				'foolframe.plugins.ssl_tools.sticky' => array(
					'type' => 'checkbox',
					'preferences' => true,
					'help' => __('Set a cookie for users that browsed the site with https:// so they get redirected to the https:// version of the site')
				),
				'foolframe.plugins.ssl_tools.enable_top_link' => array(
					'type' => 'checkbox',
					'preferences' => true,
					'help' => __('Show a link to SSL in the header if the user is browsing in http://')
				),
				'foolframe.plugins.ssl_tools.enable_bottom_link' => array(
					'type' => 'checkbox',
					'preferences' => true,
					'help' => __('Show a link to SSL in the footer if the user is browsing in http://')
				),
			)
		);

		$form['separator'] = array(
			'type' => 'separator'
		);

		$form['submit'] = array(
			'type' => 'submit',
			'value' => __('Submit'),
			'class' => 'btn btn-primary'
		);

		$form['close'] = array(
			'type' => 'close'
		);

		\Preferences::submit_auto($form);

		$data['form'] = $form;

		// create a form
		$this->_views["main_content_view"] = \View::forge("foolz/foolframe::admin/form_creator", $data);
		return new Response(\View::forge("foolz/foolframe::admin/default", $this->_views));
	}
}