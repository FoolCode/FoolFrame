<?php

namespace Foolz\Foolframe\Controller\Admin;

use \Foolz\Config\Config;

class Users extends \Foolz\Foolframe\Controller\Admin
{
	public function before(Request $request, $method)
	{
		// only mods and admins can see and edit users
		if( ! \Auth::has_access('maccess.mod'))
		{
			Response::redirect('admin');
		}

		$this->_views['controller_title'] = __('Users');

		parent::before($request, $method);
	}

	public function action_manage($page = 1)
	{
		if (intval($page) < 1)
		{
			$page = 1;
		}

		$data = [];
		$users_data = \Users::getAll($page, 40);
		$data['users'] = $users_data['result'];
		$data['count'] = $users_data['count'];

		$this->_views['method_title'] = __('Manage');
		$this->_views['main_content_view'] = \View::forge('foolz/foolframe::admin/users/manage', $data);

		return \Response::forge(\View::forge('foolz/foolframe::admin/default', $this->_views));
	}

	public function action_user($id = null)
	{
		if (intval($id) < 1)
		{
			throw new \HttpNotFoundException;
		}

		try
		{
			$data['object'] = \Foolz\Foolframe\Model\Users::getUserBy('id', $id);
			$data['object']->password = '';
		}
		catch (\Foolz\Foolframe\Model\UsersWrongIdException $e)
		{
			throw new \HttpNotFoundException;
		}

		$form = [];

		$form['open'] = array(
			'type' => 'open'
		);

		$form['paragraph'] = array(
			'type' => 'paragraph',
			'help' => __('You can customize your account here.')
		);

		$form['paragraph-2'] = array(
			'type' => 'paragraph',
			'help' => '<img src="'.\Gravatar::get_gravatar($data['object']->email).'" width="80" height="80" style="padding:2px; border: 1px solid #ccc;"/> '.
				\Str::tr(__('The avatar is automatically fetched from :gravatar, based on the user\'s registration email.'),
				array('gravatar' => '<a href="http://gravatar.com" target="_blank">Gravatar</a>'))
		);

		if (\Auth::has_access('users.change_credentials'))
		{
			$form['username'] = array(
				'type' => 'input',
				'database' => true,
				'label' => __('Username'),
				'class' => 'span3',
				'help' => __('Change the username'),
				'validation' => 'trim|max_length[32]'
			);

			$form['email'] = array(
				'type' => 'input',
				'database' => true,
				'label' => __('Email'),
				'class' => 'span3',
				'help' => __('Change the email'),
				'validation' => 'trim|max_length[32]'
			);

			$form['password'] = array(
				'type' => 'password',
				'database' => true,
				'label' => __('Password'),
				'class' => 'span3',
				'help' => __('Change the password (leave empty to not change it)'),
			);
		}

		$form['bio'] = array(
			'type' => 'textarea',
			'database' => true,
			'label' => 'Bio',
			'style' => 'height:150px;',
			'class' => 'span5',
			'help' => __('Some details about you'),
			'validation' => 'trim|max_length[360]'
		);

		$form['twitter'] = array(
			'type' => 'input',
			'database' => true,
			'label' => 'Twitter',
			'class' => 'span3',
			'help' => __('Your twitter nickname'),
			'validation' => 'trim|max_length[32]'
		);

		$form['display_name'] = array(
			'type' => 'input',
			'database' => true,
			'label' => 'Display name',
			'class' => 'span3',
			'help' => __('Alternative name in place of login username'),
			'validation' => 'trim|max_length[32]'
		);

		if (\Auth::has_access('users.change_group'))
		{
			$groups = Config::get('foolz/foolframe', 'foolauth', 'groups');
			$group_ids = [];

			foreach ($groups as $level => $group)
			{
				$group_ids[$level] = $group['name'];
			}

			$form['group_id'] = array(
				'type' => 'radio',
				'database' => true,
				'label' => 'Display name',
				'help' => __('Change the group of the user'),
				'radio_values' => $group_ids
			);
		}

		$form['submit'] = array(
			'type' => 'submit',
			'class' => 'btn btn-primary',
			'value' => __('Submit')
		);

		$form['close'] = array(
			'type' => 'close'
		);

		$data['form'] = $form;

		if (\Input::post() && ! \Security::check_token())
		{
			\Notices::set('warning', __('The security token wasn\'t found. Try resubmitting.'));
		}
		elseif (\Input::post())
		{
			$result = \Validation::form_validate($form);

			if (isset($result['error']))
			{
				\Notices::set('warning', $result['error']);
			}
			else
			{
				if (isset($result['warning']))
				{
					\Notices::set('warning', $result['warning']);
				}

				\Notices::set('success', __('Preferences updated.'));

				$user = \Foolz\Foolframe\Model\Users::getUserBy('id', $id);

				$user->save($result['success']);
				$data['object'] = $user;
				$data['object']->password = '';
			}
		}

		// create a form
		$this->_views["method_title"] = [__('Manage'), __('Edit'), $data['object']->username];
		$this->_views["main_content_view"] = \View::forge('foolz/foolframe::admin/form_creator', $data);
		return \Response::forge(\View::forge('foolz/foolframe::admin/default', $this->_views));
	}
}