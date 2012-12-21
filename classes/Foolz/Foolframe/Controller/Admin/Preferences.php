<?php

namespace Foolz\Foolframe\Controller\Admin;

use \Foolz\Config\Config;

class Preferences extends \Foolz\Foolframe\Controller\Admin
{

	public function before()
	{
		parent::before();

		if( ! \Auth::has_access('maccess.admin'))
		{
			Response::redirect('admin');
		}

		// set controller title
		$this->_views['controller_title'] = __("Preferences");
	}


	function action_theme()
	{
		$this->_views["method_title"] = __("Theme");

		$form = array();

		$form['open'] = array(
			'type' => 'open'
		);

		// build the array for the form
		$form['ff.gen.website_title'] = array(
			'type' => 'input',
			'label' => 'Title',
			'class' => 'span3',
			'preferences' => TRUE,
			'validate' => 'trim|max_length[32]',
			'help' => __('Sets the title of your site.')
		);

		// build the array for the form
		$form['ff.gen.index_title'] = array(
			'type' => 'input',
			'label' => 'Index title',
			'class' => 'span3',
			'preferences' => TRUE,
			'validate' => 'trim|max_length[32]',
			'help' => __('Sets the title displayed in the index page.')
		);

		$form['ff.lang.default'] = array(
			'type' => 'select',
			'label' => __('Default language'),
			'help' => __('The language the users will see as they reach your site.'),
			'options' => Config::get('foolz/foolframe', 'package', 'preferences.lang.available'),
			'preferences' => TRUE,
		);

		$form['separator-2'] = array(
			'type' => 'separator'
		);

		$themes = array();
		$theme_obj = new \Theme();

		foreach (Config::get('foolz/foolframe', 'package', 'modules.installed') as $module)
		{
			if ($module === 'foolz/foolframe')
			{
				continue;
			}

			$theme_obj->set_module($module);

			$identifier = Config::get($module, 'package', 'main.identifier');
			$module_name = Config::get($module, 'package', 'main.name');

			foreach($theme_obj->get_all() as $name => $theme)
			{
				$themes[] = array(
					'type' => 'checkbox',
					'label' => $theme['name'] . ' theme',
					'help' => sprintf(__('Enable %s theme'), $theme['name']),
					'array_key' => $name,
					'preferences' => TRUE,
					'checked' => defined('FOOL_PREF_THEMES_THEME_' . strtoupper($name) . '_ENABLED') ?
						constant('FOOL_PREF_THEMES_THEME_' . strtoupper($name) . '_ENABLED') : 0
				);
			}

			$form[$identifier.'.theme.active_themes'] = array(
				'type' => 'checkbox_array',
				'label' => __('Active themes'),
				'help' => \Str::tr(__('Choose the themes to make available to the users for :module. Admins are able to access any of them even if disabled.'), array('module' => '<strong>'.$module_name.'</strong>')),
				'checkboxes' => $themes
			);

			$themes_default = array();

			foreach($theme_obj->get_all() as $name => $theme)
			{
				$themes_default[$name] = $theme['name'];
			}

			$form[$identifier.'.theme.default'] = array(
				'type' => 'select',
				'label' => \Str::tr(__('Default theme for :module'), array('module' => '<strong>'.$module_name.'</strong>')),
				'help' => \Str::tr(__('The theme the users will see as they reach :module.'), array('module' => '<strong>'.$module_name.'</strong>')),
				'options' => $themes_default,
				'preferences' => TRUE,
			);
		}
		$form['ff.theme.google_analytics'] = array(
			'type' => 'input',
			'label' => __('Google Analytics code'),
			'placeholder' => 'UX-XXXXXXX-X',
			'preferences' => TRUE,
			'help' => __("Insert your Google Analytics code to get statistics."),
			'class' => 'span2'
		);

		$form['separator-3'] = array(
			'type' => 'separator'
		);

		$form['ff.theme.header_text'] = array(
			'type' => 'textarea',
			'label' => __('Header Text ("notices")'),
			'preferences' => TRUE,
			'help' => __("Inserts the text above in the header, below the nagivation links."),
			'class' => 'span5'
		);

		$form['ff.theme.header_code'] = array(
			'type' => 'textarea',
			'label' => __('Header Code'),
			'preferences' => TRUE,
			'help' => __("This will insert the HTML code inside the &lt;HEAD&gt;."),
			'class' => 'span5'
		);

		$form['ff.theme.footer_text'] = array(
			'type' => 'textarea',
			'label' => __('Footer Text'),
			'preferences' => TRUE,
			'help' => __('Credits in the footer and similar.'),
			'class' => 'span5'
		);

		$form['ff.theme.footer_code'] = array(
			'type' => 'textarea',
			'label' => __('Footer Code'),
			'preferences' => TRUE,
			'help' => __("This will insert the HTML code above after the &lt;BODY&gt;."),
			'class' => 'span5'
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


		$data['form'] = $form;


		\Preferences::submit_auto($form);

		// create a form
		$this->_views["main_content_view"] = \View::forge('foolz/foolframe::admin/form_creator', $data);
		return \Response::forge(\View::forge('foolz/foolframe::admin/default', $this->_views));
	}


	function action_advertising()
	{
		$this->_views["method_title"] = __("Advertising");

		$form = array();

		$form['open'] = array(
			'type' => 'open'
		);

		$form['ff.ads_top_banner'] = array(
			'type' => 'textarea',
			'label' => __('Top banner'),
			'help' => __('Insert the HTML code provided by your advertiser.'),
			'preferences' => TRUE,
			'validation' => 'trim',
			'class' => 'span5'
		);

		$form['ff.ads_top_banner_active'] = array(
			'type' => 'checkbox',
			'preferences' => TRUE,
			'help' => __('Enable top banner')
		);

		$form['ff.ads_bottom_banner'] = array(
			'type' => 'textarea',
			'label' => __('Bottom banner'),
			'help' => __('Insert the HTML code provided by your advertiser.'),
			'preferences' => TRUE,
			'validation' => 'trim',
			'class' => 'span5'
		);

		$form['ff.ads_bottom_banner_active'] = array(
			'type' => 'checkbox',
			'preferences' => TRUE,
			'help' => __('Enable bottom banner')
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


		$data['form'] = $form;

		\Preferences::submit_auto($form);

		// create a form
		$this->_views["main_content_view"] = \View::forge('foolz/foolframe::admin/form_creator', $data);
		return \Response::forge(\View::forge('foolz/foolframe::admin/default', $this->_views));
	}


	function action_registration()
	{
		$this->_views["method_title"] = __("Registration");

		$form = array();

		$form['open'] = array(
			'type' => 'open'
		);

		$form['ff.auth.disable_registration'] = array(
			'type' => 'checkbox',
			'preferences' => TRUE,
			'help' => __('Disable New User Registrations')
		);
		$form['ff.auth.disable_registration_email'] = array(
			'type' => 'checkbox',
			'preferences' => TRUE,
			'help' => __('Disable Email Activation')
		);

		$form['separator'] = array(
			'type' => 'separator'
		);

		$form['paragraph'] = array(
			'type' => 'paragraph',
			'help' => __('In order to use reCAPTCHA&trade; you need to sign up for the service at <a href="http://www.google.com/recaptcha">reCAPTCHA&trade;</a>, which will provide you with a public and a private key.')
		);

		$form['ff.auth.recaptcha_public'] = array(
			'type' => 'input',
			'label' => __('reCaptcha&trade; Public Key'),
			'preferences' => TRUE,
			'help' => __('Insert the public key provided by reCAPTCHA&trade;.'),
			'validation' => 'trim',
			'class' => 'span4'
		);

		$form['ff.auth.recaptcha_private'] = array(
			'type' => 'input',
			'label' => __('reCaptcha&trade; Prvate Key'),
			'preferences' => TRUE,
			'help' => __('Insert the private key provided by reCAPTCHA&trade;.'),
			'validation' => 'trim',
			'class' => 'span4'
		);

		$form['separator-2'] = array(
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


		$data['form'] = $form;

		\Preferences::submit_auto($form);

		// create a form
		$this->_views["main_content_view"] = \View::forge('foolz/foolframe::admin/form_creator', $data);
		return \Response::forge(\View::forge('foolz/foolframe::admin/default', $this->_views));
	}
}