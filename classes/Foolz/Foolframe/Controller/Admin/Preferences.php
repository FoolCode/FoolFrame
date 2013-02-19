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

	function action_general()
	{
		$this->_views["method_title"] = __("General");

		$form = [];

		$form['open'] = array(
			'type' => 'open'
		);

		// build the array for the form
		$form['foolframe.gen.website_title'] = array(
			'type' => 'input',
			'label' => 'Title',
			'class' => 'span3',
			'preferences' => TRUE,
			'validate' => 'trim|max_length[32]',
			'help' => __('Sets the title of your site.')
		);

		// build the array for the form
		$form['foolframe.gen.index_title'] = array(
			'type' => 'input',
			'label' => 'Index title',
			'class' => 'span3',
			'preferences' => TRUE,
			'validate' => 'trim|max_length[32]',
			'help' => __('Sets the title displayed in the index page.')
		);

		$form['foolframe.lang.default'] = array(
			'type' => 'select',
			'label' => __('Default language'),
			'help' => __('The language the users will see as they reach your site.'),
			'options' => Config::get('foolz/foolframe', 'package', 'preferences.lang.available'),
			'preferences' => TRUE,
		);

		$form['separator-2'] = array(
			'type' => 'separator'
		);

		$themes = [];

		foreach (Config::get('foolz/foolframe', 'config', 'modules.installed') as $module)
		{
			if ($module === 'foolz/foolframe')
			{
				continue;
			}

			$theme_loader = new \Foolz\Theme\Loader();
			$theme_loader->addDir('default', VENDPATH.$module.'/'.Config::get($module, 'package', 'directories.themes'));
			$themes = $theme_loader->getAll();

			$identifier = Config::get($module, 'package', 'main.identifier');
			$module_name = Config::get($module, 'package', 'main.name');

			$theme_checkboxes = [];
			foreach($themes['default'] as $name => $theme)
			{
				$theme_checkboxes[] = array(
					'type' => 'checkbox_array_value',
					'label' => $theme->getConfig('name'),
					'help' => sprintf(__('Enable %s theme'), $theme->getConfig('name')),
					'array_key' => $theme->getConfig('name'),
					'preferences' => TRUE
				);
			}

			$form[$identifier.'.theme.active_themes'] = array(
				'type' => 'checkbox_array',
				'label' => __('Active themes'),
				'help' => \Str::tr(__('Choose the themes to make available to the users for :module. Admins are able to access any of them even if disabled.'), array('module' => '<strong>'.$module_name.'</strong>')),
				'checkboxes' => $theme_checkboxes
			);

			$themes_default = [];

			foreach($themes['default'] as $name => $theme)
			{
				$themes_default[$name] = $theme->getConfig('name');
			}

			$form[$identifier.'.theme.default'] = array(
				'type' => 'select',
				'label' => \Str::tr(__('Default theme for :module'), array('module' => '<strong>'.$module_name.'</strong>')),
				'help' => \Str::tr(__('The theme the users will see as they reach :module.'), array('module' => '<strong>'.$module_name.'</strong>')),
				'options' => $themes_default,
				'preferences' => TRUE,
			);
		}
		$form['foolframe.theme.google_analytics'] = array(
			'type' => 'input',
			'label' => __('Google Analytics code'),
			'placeholder' => 'UX-XXXXXXX-X',
			'preferences' => TRUE,
			'help' => __("Insert your Google Analytics code."),
			'class' => 'span2'
		);

		$form['separator-3'] = array(
			'type' => 'separator'
		);

		$form['foolframe.theme.header_text'] = array(
			'type' => 'textarea',
			'label' => __('Header Text ("notices")'),
			'preferences' => TRUE,
			'help' => __("Inserts the text above in the header, below the navigation links."),
			'class' => 'span5'
		);

		$form['foolframe.theme.header_code'] = array(
			'type' => 'textarea',
			'label' => __('Header Code'),
			'preferences' => TRUE,
			'help' => __("This will insert the HTML code inside the &lt;HEAD&gt;."),
			'class' => 'span5'
		);

		$form['foolframe.theme.footer_text'] = array(
			'type' => 'textarea',
			'label' => __('Footer Text'),
			'preferences' => TRUE,
			'help' => __('The text to put in the footer, such as credits and similar.'),
			'class' => 'span5'
		);

		$form['foolframe.theme.footer_code'] = array(
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

		$form = [];

		$form['open'] = array(
			'type' => 'open'
		);

		$form['foolframe.ads_top_banner'] = array(
			'type' => 'textarea',
			'label' => __('Top banner'),
			'help' => __('Insert the HTML code provided by your advertiser.'),
			'preferences' => TRUE,
			'validation' => 'trim',
			'class' => 'span5'
		);

		$form['foolframe.ads_top_banner_active'] = array(
			'type' => 'checkbox',
			'preferences' => TRUE,
			'help' => __('Enable top banner')
		);

		$form['foolframe.ads_bottom_banner'] = array(
			'type' => 'textarea',
			'label' => __('Bottom banner'),
			'help' => __('Insert the HTML code provided by your advertiser.'),
			'preferences' => TRUE,
			'validation' => 'trim',
			'class' => 'span5'
		);

		$form['foolframe.ads_bottom_banner_active'] = array(
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

		$form = [];

		$form['open'] = array(
			'type' => 'open'
		);

		$form['foolframe.auth.disable_registration'] = array(
			'type' => 'checkbox',
			'preferences' => TRUE,
			'help' => __('Disable New User Registrations')
		);
		$form['foolframe.auth.disable_registration_email'] = array(
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

		$form['foolframe.auth.recaptcha_public'] = array(
			'type' => 'input',
			'label' => __('reCaptcha&trade; Public Key'),
			'preferences' => TRUE,
			'help' => __('Insert the public key provided by reCAPTCHA&trade;.'),
			'validation' => 'trim',
			'class' => 'span4'
		);

		$form['foolframe.auth.recaptcha_private'] = array(
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