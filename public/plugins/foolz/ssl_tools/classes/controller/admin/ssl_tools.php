<?php

namespace Foolframe\Plugins\Ssl_Tools;

if (!defined('DOCROOT'))
	exit('No direct script access allowed');

class Controller_Plugin_Ff_Ssl_Tools_Admin_Ssl_Tools extends \Controller_Admin
{
	
	public function before()
	{
		if ( ! \Auth::has_access('maccess.admin'))
		{
			\Response::redirect('admin');
		}
		
		parent::before();
		
		$this->_views['controller_title'] = __('SSL Tools');
	}

	public function action_manage()
	{
		$this->_views['method_title'] = 'SSL';

		$form = array();

		$form['open'] = array(
			'type' => 'open'
		);

		$form['ff.plugins.ssl_tools.available'] = array(
			'type' => 'checkbox',
			'preferences' => TRUE,
			'help' => __('Does the server have SSL available (does your site support https:// protocol)?'),
			'sub' => array(
				'ff.plugins.ssl_tools.force_for_logged' => array(
					'type' => 'checkbox',
					'preferences' => TRUE,
					'help' => __('Redirect the logged in users to the SSL version of the site')
				),
				'ff.plugins.ssl_tools.force_everyone' => array(
					'type' => 'checkbox',
					'preferences' => TRUE,
					'help' => __('Redirect every user to the SSL version of the site')
				),
				'ff.plugins.ssl_tools.sticky' => array(
					'type' => 'checkbox',
					'preferences' => TRUE,
					'help' => __('Set a cookie for users that browsed the site with https:// so they get redirected to the https:// version of the site')
				),
				'ff.plugins.ssl_tools.enable_top_link' => array(
					'type' => 'checkbox',
					'preferences' => TRUE,
					'help' => __('Show a link to SSL in the header if the user is browsing in http://')
				),
				'ff.plugins.ssl_tools.enable_bottom_link' => array(
					'type' => 'checkbox',
					'preferences' => TRUE,
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
		$this->_views["main_content_view"] = \View::forge("admin/form_creator", $data);
		return \Response::forge(\View::forge("admin/default", $this->_views));
	}

}