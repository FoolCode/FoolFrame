<?php

namespace Foolz\Foolframe\Controller;

use \Foolz\Foolframe\Model\Config;
use \Foolz\Foolframe\Model\DoctrineConnection as DC;
use Foolz\Foolframe\Model\Notices;
use \Foolz\Foolframe\Model\System as System;
use Foolz\Foolframe\Model\Validation\ActiveConstraint\Trim;
use Foolz\Foolframe\Model\Validation\Validator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

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

        $this->builder->getProps()->addTitle(_i('FoolFrame Installation'));
        $this->param_manager->setParam('controller_title', _i('FoolFrame Installation'));

        $this->builder->createPartial('navbar', 'install/navbar');
    }

    public function process($action)
    {
        $procedure = [
            'welcome' => _i('Welcome'),
            'system_check' => _i('System Check'),
            'database_setup' => _i('Database Setup'),
            'create_admin' => _i('Admin Account'),
            'modules' => _i('Install Modules'),
            'complete' => _i('Congratulations'),
        ];

        $this->builder->createPartial('sidebar', 'install/sidebar')
            ->getParamManager()->setParams(['sidebar' => $procedure, 'current' => $action]);
    }

    public function action_404()
    {
        Notices::set('warning', _i('Page not found.'));
        return new Response($this->builder->build(), 404);
    }

    public function action_index()
    {
        $this->process('welcome');
        $this->param_manager->setParam('method_title', _i('Welcome'));

        $this->builder->createPartial('body', 'install/welcome');
        return new Response($this->builder->build());
    }

    public function action_system_check()
    {
        $data['system'] = \Foolz\Foolframe\Model\System::environment();

        $this->process('system_check');
        $this->param_manager->setParam('method_title', _i('System Check'));

        $this->builder->createPartial('body', 'install/system_check')
            ->getParamManager()->setParams($data);
        return new Response($this->builder->build());
    }

    public function action_database_setup()
    {
        if (\Input::post()) {
            $validator = new Validator();
            $validator
                ->add('hostname', _i('Database Hostname'), [new Trim(), new Assert\NotBlank()])
                ->add('prefix', _i('Table Prefix'), [new Trim()])
                ->add('username', _i('Username'), [new Trim(), new Assert\NotBlank()])
                ->add('database', _i('Database name'), [new Trim(), new Assert\NotBlank()]);

            $validator->validate(\Input::post());

            if (!$validator->getViolations()->count()) {
                $input = $validator->getFinalValues();
                $input['password'] = \Input::post('password');
                $input['type'] = \Input::post('type');

                if (\Foolz\Foolframe\Model\Install::check_database($input)) {
                    \Foolz\Foolframe\Model\Install::setup_database($input);

                    $sm = \Foolz\Foolframe\Model\SchemaManager::forge(DC::forge(), DC::getPrefix());
                    \Foolz\Foolframe\Model\Schema::load($sm);
                    $sm->commit();

                    \Foolz\Foolframe\Model\Install::create_salts();

                    \Response::redirect('install/create_admin');
                } else {
                    Notices::set('warning', _i('Connection to specified database failed. Please check your connection details again.'));
                }
            } else {
                Notices::set('warning', $validator->getViolations()->getText());
            }
        }

        $this->process('database_setup');
        $this->param_manager->setParam('method_title', _i('Database Setup'));

        $this->builder->createPartial('body', 'install/database_setup');
        return new Response($this->builder->build());
    }

    public function action_create_admin()
    {
        // if an admin account exists, lock down this step and redirect to the next step instead
        $check_users = \Foolz\Foolframe\Model\Users::getAll();

        if ($check_users['count'] > 0) {
            \Response::redirect('install/modules');
        }

        if (\Input::post()) {
            $validator = new Validator();
            $validator
                ->add('username', _i('Username'), [new Trim(), new Assert\NotBlank(), new Assert\Length(['min' => 4, 'max' => 32])])
                ->add('email', _i('Email'), [new Trim(), new Assert\NotBlank(), new Assert\Email()])
                ->add('password', _i('Password'), [new Trim(), new Assert\NotBlank(), new Assert\Length(['min' => 4, 'max' => 64])]);

            $validator->validate(\Input::post());

            if (!$validator->getViolations()->count()) {
                $input = $validator->getFinalValues();

                list($id, $activation_key) = \Auth::create_user($input['username'], $input['password'], $input['email']);
                \Auth::activate_user($id, $activation_key);
                \Auth::force_login($id);
                $user = \Foolz\Foolframe\Model\Users::getUser();
                $user->save(['group_id' => 100]);

                \Response::redirect('install/modules');
            } else {
                Notices::set('warning', $validator->getViolations()->getText());
            }
        }

        $this->process('create_admin');
        $this->param_manager->setParam('method_title', _i('Admin Account'));

        $this->builder->createPartial('body', 'install/create_admin');
        return new Response($this->builder->build());
    }

    public function action_modules()
    {
        $data = ['modules' => \Foolz\Foolframe\Model\Install::modules()];

        if (\Input::post()) {
            \Config::load('foolframe', 'foolframe');

            $modules = ['foolframe' => 'foolz/foolframe'];

            $sm = \Foolz\Foolframe\Model\SchemaManager::forge(DC::forge(), DC::getPrefix());
            \Foolz\Foolframe\Model\Schema::load($sm);

            if (\Input::post('foolfuuka')) {
                $modules['foolfuuka'] = 'foolz/foolfuuka';

                \Foolz\Foolfuuka\Model\Schema::load($sm);
            }

            if (\Input::post('foolslide')) {
                $modules['foolslide'] = 'foolz/foolslide';
            }

            if (\Input::post('foolstatus')) {
                $modules['foolstatus'] = 'foolz/foolstatus';

                \Foolz\Foolstatus\Model\Schema::load($sm);
            }

            $sm->commit();

            if (count($modules) > 1) {
                Config::set('foolz/foolframe', 'config', 'modules.installed', $modules);
                Config::set('foolz/foolframe', 'config', 'install.installed', true);
                Config::save('foolz/foolframe', 'config');

                $this->process('complete');
                $this->param_manager->setParam('method_title', _i('Congratulations'));

                $this->builder->createPartial('body', 'install/complete');
                return new Response($this->builder->build());
            } else {
                Notices::set('warning', _i('Please select at least one module.'));
            }
        }

        $this->process('modules');
        $this->param_manager->setParam('method_title', _i('Install Modules'));

        $this->builder->createPartial('body', 'install/modules')
            ->getParamManager()->setParams($data);
        return new Response($this->builder->build());
    }
}
