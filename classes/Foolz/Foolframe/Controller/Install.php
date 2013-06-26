<?php

namespace Foolz\Foolframe\Controller;

use \Foolz\Config\Config;
use \Foolz\Foolframe\Model\DoctrineConnection as DC;
use Foolz\Foolframe\Model\Notices;
use \Foolz\Foolframe\Model\System as System;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Install
{
	/**
	 * @var \Foolz\Theme\Theme
	 */
	protected $theme;

	/**
	 * @var \Foolz\Theme\Builder
	 */
	protected $builder;

	/**
	 * @var \Foolz\Theme\ParamManager
	 */
	protected $param_manager;

	public function before(Request $request)
	{
		$theme_instance = \Foolz\Theme\Loader::forge('foolframe_admin');
		$theme_instance->addDir(VENDPATH.'foolz/foolframe/public/themes-admin/');
		$theme_instance->setBaseUrl(\Uri::base().'foolframe/');
		$theme_instance->setPublicDir(DOCROOT.'foolframe/');
		$this->theme = $theme_instance->get('foolz/foolframe-theme-admin');
		$this->builder = $this->theme->createBuilder();
		$this->param_manager = $this->builder->getParamManager();
		$this->builder->createLayout('base');

		$this->builder->getProps()->addTitle(__('FoolFrame Installation'));
		$this->param_manager->setParam('controller_title', __('FoolFrame Installation'));

		$this->builder->createPartial('navbar', 'install/navbar');
	}

	public function process($action)
	{
		$procedure = array(
			'welcome' => __('Welcome'),
			'system_check' => __('System Check'),
			'database_setup' => __('Database Setup'),
			'create_admin' => __('Admin Account'),
			'modules' => __('Install Modules'),
			'complete' => __('Congratulations'),
		);

		$this->builder->createPartial('sidebar', 'install/sidebar')
			->getParamManager()->setParams(['sidebar' => $procedure, 'current' => $action]);
	}


	public function action_index()
	{
		$data = [];

		$this->process('welcome');
		$this->param_manager->setParam('method_title', __('Welcome'));

		$this->builder->createPartial('body', 'install/welcome')
			->getParamManager()->setParams($data);
		return new Response($this->builder->build());
	}


	public function action_system_check()
	{
		$data['system'] = \Foolz\Foolframe\Model\System::environment();

		$this->process('system_check');
		$this->param_manager->setParam('method_title', __('System Check'));

		$this->builder->createPartial('body', 'install/system_check');
		return new Response($this->builder->build());
	}


	public function action_database_setup()
	{
		if (\Input::post())
		{
			$val = \Validation::forge('database');
			$val->add_field('hostname', __('Hostname'), 'required|trim');
			$val->add_field('prefix', __('Prefix'), 'trim');
			$val->add_field('username', __('Username'), 'required|trim');
			$val->add_field('database', __('Database name'), 'required|trim');

			if ($val->run())
			{
				$input = $val->input();
				$input['password'] = \Input::post('password');
				$input['type'] = \Input::post('type');

				if (\Foolz\Foolframe\Model\Install::check_database($input))
				{
					\Foolz\Foolframe\Model\Install::setup_database($input);

					$sm = \Foolz\Foolframe\Model\SchemaManager::forge(DC::forge(), DC::getPrefix());
					\Foolz\Foolframe\Model\Schema::load($sm);
					$sm->commit();

					\Foolz\Foolframe\Model\Install::create_salts();

					\Response::redirect('install/create_admin');
				}
				else
				{
					Notices::set('warning', __('Connection to specified database failed. Please check your connection details again.'));
				}
			}
			else
			{
				Notices::set('warning', $val->error());
			}
		}

		$this->process('database_setup');
		$this->param_manager->setParam('method_title', __('Database Setup'));

		$this->builder->createPartial('body', 'install/database_setup');
		return new Response($this->builder->build());
	}


	public function action_create_admin()
	{
		// if an admin account exists, lock down this step and redirect to the next step instead
		Config::load('foolauth', 'foolauth');
		$check_users = \Foolz\Foolframe\Model\Users::getAll();

		if ($check_users['count'] > 0)
		{
			\Response::redirect('install/modules');
		}

		if (\Input::post())
		{
			$val = \Validation::forge('database');
			$val->add_field('username', __('Username'), 'required|trim|min_length[4]|max_length[32]');
			$val->add_field('email', __('Email'), 'required|trim|valid_email');
			$val->add_field('password', __('Password'), 'required|min_length[4]|max_length[32]');
			$val->add_field('confirm_password', __('Confirm password'), 'required|match_field[password]');

			if ($val->run())
			{
				$input = $val->input();

				list($id, $activation_key) = \Auth::create_user($input['username'], $input['password'], $input['email']);
				\Auth::activate_user($id, $activation_key);
				\Auth::force_login($id);
				$user = \Foolz\Foolframe\Model\Users::getUser();
				$user->save(['group_id' => 100]);

				\Response::redirect('install/modules');
			}
			else
			{
				Notices::set('warning', $val->error());
			}
		}

		$this->process('create_admin');
		$this->param_manager->setParam('method_title', __('Admin Account'));

		$this->builder->createPartial('body', 'install/database_setup');
		return new Response($this->builder->build());
	}


	public function action_modules()
	{
		$data = [];
		$data['modules'] = \Foolz\Foolframe\Model\Install::modules();

		if (\Input::post())
		{
			\Config::load('foolframe', 'foolframe');

			$modules = ['foolframe' => 'foolz/foolframe'];

			$sm = \Foolz\Foolframe\Model\SchemaManager::forge(DC::forge(), DC::getPrefix());
			\Foolz\Foolframe\Model\Schema::load($sm);

			if (\Input::post('foolfuuka'))
			{
				$modules['foolfuuka'] = 'foolz/foolfuuka';

				\Foolz\Foolfuuka\Model\Schema::load($sm);
			}

			if (\Input::post('foolslide'))
			{
				$modules['foolslide'] = 'foolz/foolslide';
			}

			if (\Input::post('foolstatus'))
			{
				$modules['foolstatus'] = 'foolz/foolstatus';

				\Foolz\Foolstatus\Model\Schema::load($sm);
			}

			$sm->commit();

			if (count($modules) > 1)
			{
				Config::set('foolz/foolframe', 'config', 'modules.installed', $modules);
				Config::save('foolz/foolframe', 'config');

				\Response::redirect('install/complete');
			}
			else
			{
				Notices::set('warning', __('Please select at least one module.'));
			}
		}

		$this->process('modules');
		$this->_view_data['method_title'] = __('Install Modules');
		$this->_view_data['main_content_view'] = \View::forge('install::install/modules', $data);

		return \Response::forge(\View::forge('install::default', $this->_view_data));
	}


	public function action_complete()
	{
		// lock down the install system
		Config::set('foolz/foolframe', 'config', 'install.installed', true);
		Config::save('foolz/foolframe', 'config');

		$this->process('complete');
		$this->_view_data['method_title'] = __('Congratulations');
		$this->_view_data['main_content_view'] = \View::forge('install::install/complete');

		return \Response::forge(\View::forge('install::default', $this->_view_data));
	}

}