<?php

namespace Foolz\Foolframe\Controller\Admin;

use \Foolz\Config\Config;
use Foolz\Foolframe\Controller\Admin;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Preferences extends Admin
{
	public function before(Request $request)
	{
		parent::before($request);

		if ( ! \Auth::has_access('maccess.admin'))
		{
			Response::redirect('admin');
		}

		// set controller title
		$this->param_manager->setParam('controller_title', __('Preferences'));
	}

	function action_general()
	{
		$this->param_manager->setParam('method_title', __('General'));

		$form = [];

		$form['open'] = array(
			'type' => 'open'
		);

		// build the array for the form
		$form['foolframe.gen.website_title'] = array(
			'type' => 'input',
			'label' => 'Title',
			'class' => 'span3',
			'preferences' => true,
			'validate' => 'trim|max_length[32]',
			'help' => __('Sets the title of your site.')
		);

		// build the array for the form
		$form['foolframe.gen.index_title'] = array(
			'type' => 'input',
			'label' => 'Index title',
			'class' => 'span3',
			'preferences' => true,
			'validate' => 'trim|max_length[32]',
			'help' => __('Sets the title displayed in the index page.')
		);

		$form['foolframe.lang.default'] = array(
			'type' => 'select',
			'label' => __('Default language'),
			'help' => __('The language the users will see as they reach your site.'),
			'options' => Config::get('foolz/foolframe', 'package', 'preferences.lang.available'),
			'preferences' => true,
		);

		$form['separator-2'] = array(
			'type' => 'separator'
		);

		foreach (Config::get('foolz/foolframe', 'config', 'modules.installed') as $module)
		{
			if ($module === 'foolz/foolframe')
			{
				continue;
			}

			$theme_loader = new \Foolz\Theme\Loader();
			$theme_loader->addDir(VENDPATH.$module.'/'.Config::get($module, 'package', 'directories.themes'));
			$themes = $theme_loader->getAll();

			$module_name = Config::get($module, 'package', 'main.name');

			$theme_checkboxes = [];

			foreach($themes as $name => $theme)
			{
				$theme_checkboxes[] = array(
					'type' => 'checkbox_array_value',
					'label' => $theme->getConfig('name'),
					'help' => sprintf(__('Enable %s theme'), $theme->getConfig('extra.name')),
					'array_key' => $theme->getConfig('name'),
					'preferences' => true
				);
			}

			$form[strtolower($module_name).'.theme.active_themes'] = array(
				'type' => 'checkbox_array',
				'label' => __('Active themes'),
				'help' => \Str::tr(__('Choose the themes to make available to the users for :module. Admins are able to access any of them even if disabled.'),
					array('module' => '<strong>'.$module_name.'</strong>')),
				'checkboxes' => $theme_checkboxes
			);

			$themes_default = [];

			foreach($themes as $name => $theme)
			{
				if ($theme->getConfig('extra.styles', false))
				{
					foreach ($theme->getConfig('extra.styles') as $style => $style_name)
					{
						$themes_default[$name.'/'.$style] = $theme->getConfig('extra.name').' - '.$style_name;
					}
				}
				else
				{
					$themes_default[$name] = $theme->getConfig('extra.name');
				}

			}

			$form[strtolower($module_name).'.theme.default'] = array(
				'type' => 'select',
				'label' => \Str::tr(__('Default theme for :module'), array('module' => '<strong>'.$module_name.'</strong>')),
				'help' => \Str::tr(__('The theme the users will see as they reach :module.'), array('module' => '<strong>'.$module_name.'</strong>')),
				'options' => $themes_default,
				'preferences' => true,
			);
		}

		$form['foolframe.theme.google_analytics'] = array(
			'type' => 'input',
			'label' => __('Google Analytics code'),
			'placeholder' => 'UX-XXXXXXX-X',
			'preferences' => true,
			'help' => __("Insert your Google Analytics code."),
			'class' => 'span2'
		);

		$form['separator-3'] = array(
			'type' => 'separator'
		);

		$form['foolframe.theme.header_text'] = array(
			'type' => 'textarea',
			'label' => __('Header Text ("notices")'),
			'preferences' => true,
			'help' => __("Inserts the text above in the header, below the navigation links."),
			'class' => 'span5'
		);

		$form['foolframe.theme.header_code'] = array(
			'type' => 'textarea',
			'label' => __('Header Code'),
			'preferences' => true,
			'help' => __("This will insert the HTML code inside the &lt;HEAD&gt;."),
			'class' => 'span5'
		);

		$form['foolframe.theme.footer_text'] = array(
			'type' => 'textarea',
			'label' => __('Footer Text'),
			'preferences' => true,
			'help' => __('The text to put in the footer, such as credits and similar.'),
			'class' => 'span5'
		);

		$form['foolframe.theme.footer_code'] = array(
			'type' => 'textarea',
			'label' => __('Footer Code'),
			'preferences' => true,
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
		$this->builder->createPartial('body', 'form_creator')
			->getParamManager()->setParams($data);
		return new Response($this->builder->build());
	}

	function action_advertising()
	{
		$this->param_manager->setParam('method_title', __('Advertising'));

		$form = [];

		$form['open'] = array(
			'type' => 'open'
		);

		$form['foolframe.ads_top_banner'] = array(
			'type' => 'textarea',
			'label' => __('Top banner'),
			'help' => __('Insert the HTML code provided by your advertiser.'),
			'preferences' => true,
			'validation' => 'trim',
			'class' => 'span5'
		);

		$form['foolframe.ads_top_banner_active'] = array(
			'type' => 'checkbox',
			'preferences' => true,
			'help' => __('Enable top banner')
		);

		$form['foolframe.ads_bottom_banner'] = array(
			'type' => 'textarea',
			'label' => __('Bottom banner'),
			'help' => __('Insert the HTML code provided by your advertiser.'),
			'preferences' => true,
			'validation' => 'trim',
			'class' => 'span5'
		);

		$form['foolframe.ads_bottom_banner_active'] = array(
			'type' => 'checkbox',
			'preferences' => true,
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
		$this->builder->createPartial('body', 'form_creator')
			->getParamManager()->setParams($data);
		return new Response($this->builder->build());
	}

	function action_registration()
	{
		$this->param_manager->setParam('method_title', __('Registration'));

		$form = [];

		$form['open'] = array(
			'type' => 'open'
		);

		$form['foolframe.auth.disable_registration'] = array(
			'type' => 'checkbox',
			'preferences' => true,
			'help' => __('Disable New User Registrations')
		);
		$form['foolframe.auth.disable_registration_email'] = array(
			'type' => 'checkbox',
			'preferences' => true,
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
			'preferences' => true,
			'help' => __('Insert the public key provided by reCAPTCHA&trade;.'),
			'validation' => 'trim',
			'class' => 'span4'
		);

		$form['foolframe.auth.recaptcha_private'] = array(
			'type' => 'input',
			'label' => __('reCaptcha&trade; Prvate Key'),
			'preferences' => true,
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
		$this->builder->createPartial('body', 'form_creator')
			->getParamManager()->setParams($data);
		return new Response($this->builder->build());
	}
}