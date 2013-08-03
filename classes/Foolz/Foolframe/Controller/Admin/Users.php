<?php

namespace Foolz\Foolframe\Controller\Admin;

use Foolz\Foolframe\Model\Legacy\Config;
use Foolz\Foolframe\Model\Validation\ActiveConstraint\Trim;
use Foolz\Foolframe\Model\Validation\Validator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Constraints as Assert;


class Users extends \Foolz\Foolframe\Controller\Admin
{
    public function before(Request $request)
    {
        // only mods and admins can see and edit users
        if(!\Auth::has_access('maccess.mod')) {
            Response::redirect('admin');
        }

        parent::before($request);

        $this->param_manager->setParam('controller_title', _i('Users'));
    }

    public function action_manage($page = 1)
    {
        if (intval($page) < 1) {
            $page = 1;
        }

        $data = [];
        $users_data = \Users::getAll($page, 40);
        $data['users'] = $users_data['result'];
        $data['count'] = $users_data['count'];

        $this->param_manager->setParam('method_title', _i('Manage'));
        $this->builder->createPartial('body', 'users/manage')
            ->getParamManager()->setParams($data);

        return new Response($this->builder->build());
    }

    public function action_user($id = null)
    {
        if (intval($id) < 1) {
            throw new NotFoundHttpException;
        }

        try {
            $data['object'] = \Foolz\Foolframe\Model\Users::getUserBy('id', $id);
            $data['object']->password = '';
        } catch (\Foolz\Foolframe\Model\UsersWrongIdException $e) {
            throw new NotFoundHttpException;
        }

        $form = [];

        $form['open'] = array(
            'type' => 'open'
        );

        $form['paragraph'] = array(
            'type' => 'paragraph',
            'help' => _i('You can customize your account here.')
        );

        $form['paragraph-2'] = array(
            'type' => 'paragraph',
            'help' => '<img src="'.\Gravatar::get_gravatar($data['object']->email).'" width="80" height="80" style="padding:2px; border: 1px solid #ccc;"/> '.
                _i('The avatar is automatically fetched from %s, based on the user\'s registration email.',
                '<a href="http://gravatar.com" target="_blank">Gravatar</a>')
        );

        if (\Auth::has_access('users.change_credentials')) {
            $form['username'] = array(
                'type' => 'input',
                'database' => true,
                'label' => _i('Username'),
                'class' => 'span3',
                'help' => _i('Change the username'),
                'validation' => [new Trim(), new Assert\Length(['max' => 32])]
            );

            $form['email'] = array(
                'type' => 'input',
                'database' => true,
                'label' => _i('Email'),
                'class' => 'span3',
                'help' => _i('Change the email'),
                'validation' => [new Trim(), new Assert\Length(['max' => 32])]
            );

            $form['password'] = array(
                'type' => 'password',
                'database' => true,
                'label' => _i('Password'),
                'class' => 'span3',
                'help' => _i('Change the password (leave empty to not change it)'),
            );
        }

        $form['bio'] = array(
            'type' => 'textarea',
            'database' => true,
            'label' => 'Bio',
            'style' => 'height:150px;',
            'class' => 'span5',
            'help' => _i('Some details about you'),
            'validation' => [new Trim(), new Assert\Length(['max' => 360])]
        );

        $form['twitter'] = array(
            'type' => 'input',
            'database' => true,
            'label' => 'Twitter',
            'class' => 'span3',
            'help' => _i('Your twitter nickname'),
            'validation' => [new Trim(), new Assert\Length(['max' => 32])]
        );

        $form['display_name'] = array(
            'type' => 'input',
            'database' => true,
            'label' => 'Display name',
            'class' => 'span3',
            'help' => _i('Alternative name in place of login username'),
            'validation' => [new Trim(), new Assert\Length(['max' => 32])]
        );

        if (\Auth::has_access('users.change_group')) {
            $groups = Config::get('foolz/foolframe', 'foolauth', 'groups');
            $group_ids = [];

            foreach ($groups as $level => $group) {
                $group_ids[$level] = $group['name'];
            }

            $form['group_id'] = array(
                'type' => 'radio',
                'database' => true,
                'label' => 'Display name',
                'help' => _i('Change the group of the user'),
                'radio_values' => $group_ids
            );
        }

        $form['submit'] = array(
            'type' => 'submit',
            'class' => 'btn btn-primary',
            'value' => _i('Submit')
        );

        $form['close'] = array(
            'type' => 'close'
        );

        $data['form'] = $form;

        if (\Input::post() && !\Security::check_token()) {
            \Notices::set('warning', _i('The security token wasn\'t found. Try resubmitting.'));
        } elseif (\Input::post()) {
            $result = Validator::formValidate($form);

            if (isset($result['error'])) {
                \Notices::set('warning', $result['error']);
            } else {
                if (isset($result['warning'])) {
                    \Notices::set('warning', $result['warning']);
                }

                \Notices::set('success', _i('Preferences updated.'));

                $user = \Foolz\Foolframe\Model\Users::getUserBy('id', $id);

                $user->save($result['success']);
                $data['object'] = $user;
                $data['object']->password = '';
            }
        }

        // create a form
        $this->param_manager->setParam('method_title', [_i('Manage'), _i('Edit'), $data['object']->username]);
        $this->builder->createPartial('body', 'form_creator')
            ->getParamManager()->setParams($data);

        return new Response($this->builder->build());
    }
}
