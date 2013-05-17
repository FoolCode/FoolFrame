<?php

namespace Foolz\Foolframe\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Account extends \Foolz\Foolframe\Controller\Admin
{
	public function before(Request $request)
	{
		parent::before($request);

		$this->_views['controller_title'] = __('Account');
	}

	public function action_login()
	{
		// redirect user to admin panel
		if (\Auth::has_access('maccess.user'))
		{
			\Response::redirect('admin');
		}

		$data = [];

		// the login button has been submitted - authenticate username and password
		if (\Input::post() && ! \Security::check_token())
		{
			\Notices::set('error', __('The security token was not found. Please try again.'));
		}
		elseif (\Input::post())
		{
			// load authentication instance
			$auth = \Auth::instance();

			// verify credentials
			try
			{
				$auth->login();
				\Response::redirect('admin');
			}
			catch (\Auth\FoolUserWrongUsernameOrPassword $e)
			{
				// invalid username or password was entered
				$data['username'] = \Input::post('username');
				\Notices::set('error', __('You have entered an invalid username and/or password. Please try again.'));
			}
			catch (\Auth\FoolUserLimitExceeded $e)
			{
				// account has been locked due to excess authentication failures
				$data['username'] = \Input::post('username');
				\Notices::set('error', \Str::tr(__('After :number failed login attempts, this account has been locked. In order to unlock your account, please use the password reset system.'), array('number' => Config::get('foolz/foolframe', 'foolauth', 'attempts_to_lock'))));
			}
		}

		// generate login form
		$this->_views['method_title'] = __('Login');
		$this->_views['main_content_view'] = \View::forge('foolz/foolframe::admin/account/login');

		return new Response(\View::forge('foolz/foolframe::admin/account', $this->_views));
	}

	public function action_logout()
	{
		if ( ! \Security::check_token(\Input::get('token')))
		{
			die('The security token is invalid.');
		}

		\Auth::logout(false);
		\Response::redirect('admin');
	}

	/**
	 * Log out from all the devices
	 */
	public function action_logout_all()
	{
		if ( ! \Security::check_token(\Input::get('token')))
		{
			die('The security token didn\'t match or has expired.');
		}

		\Auth::logout(true);
		\Response::redirect('admin');
	}

	public function action_register()
	{
		if (\Auth::has_access('maccess.user'))
		{
			\Response::redirect('admin');
		}

		if (\Preferences::get('foolframe.auth.disable_registration'))
		{
			throw new HttpNotFoundException;
		}

		if (\Input::post() && ! \Security::check_token())
		{
			\Notices::set('warning', __('The security token wasn\'t found. Try resubmitting.'));
		}
		elseif (\Input::post())
		{

			$val = \Validation::forge('register');
			$val->add_field('username', __('Username'), 'required|trim|min_length[4]|max_length[32]');
			$val->add_field('email', __('Email'), 'required|trim|valid_email');
			$val->add_field('password', __('Password'), 'required|min_length[4]|max_length[32]');
			$val->add_field('confirm_password', __('Confirm password'), 'required|match_field[password]');

			$recaptcha = ! \ReCaptcha::available() || \ReCaptcha::instance()->check_answer(\Input::ip(), \Input::post('recaptcha_challenge_field'), \Input::post('recaptcha_response_field'));

			if($val->run() && $recaptcha)
			{
				$input = $val->input();

				try
				{
					list($id, $activation_key) = \Auth::create_user($input['username'], $input['password'], $input['email']);
				}
				catch (\Auth\FoolUserUpdateException $e)
				{
					\Notices::setFlash('error', $e->getMessage());
					\Response::redirect('admin/account/register');
				}

				// activate or send activation email
				if ( ! $activation_key)
				{
					\Notices::setFlash('success', __('Congratulations! You have successfully registered.'));
				}
				else
				{
					$from = 'no-reply@'.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'no-email-assigned');

					$title = \Preferences::get('foolframe.gen.website_title').' - '.__('Account Activation');

					$content = \View::forge('foolz/foolframe::admin/account/email_activation', array(
						'title' => $title,
						'site' => \Preferences::get('foolframe.gen.website_title'),
						'username' => $input['username'],
						'link' => \Uri::create('admin/account/activate/'.$id.'/'.$activation_key)
					));

					\Package::load('email');
					$sendmail = \Email::forge();
					$sendmail->from($from, \Preferences::get('foolframe.gen.website_title'))
						->subject($title)
						->to($input['email'])
						->html_body(\View::forge('foolz/foolframe::email_default', array('title' => $title, 'content' => $content)));

					try
					{
						$sendmail->send();
					}
					catch(\EmailSendingFailedException $e)
					{
						// the email driver was unable to send the email. the account will be activated automatically.
						\Auth::activate_user($id, $activation_key);
						\Notices::setFlash('success', __('Congratulations! You have successfully registered.'));
						\Log::error(\Str::tr('The system was unable to send an activation email to :username. The account was activated automatically.', array('username' => $input['username'])));
						\Response::redirect('admin/account/login');
					}

					\Notices::setFlash('success', __('Congratulations! You have successfully registered. Please check your email to activate your account.'));
				}

				\Response::redirect('admin/account/login');
			}
			else
			{
				$error = $val->error();
				if ( ! $recaptcha)
				{
					$error[] = __('The reCAPTCHA code entered does not match the one displayed.');
				}

				\Notices::set('error', implode(' ', $error));
			}
		}

		$this->_views['method_title'] = __('Register');
		$this->_views['main_content_view'] = \View::forge('foolz/foolframe::admin/account/register');

		return new Response(\View::forge('foolz/foolframe::admin/account', $this->_views));
	}

	public function action_activate($id, $activation_key)
	{
		if (\Auth::has_access('maccess.user'))
		{
			\Response::redirect('admin');
		}

		if (\Auth::activate_user($id, $activation_key))
		{
			\Notices::setFlash('success', __('Your account has been activated. You are now able to login and access the control panel.'));
			\Response::redirect('admin/account/login');
		}

		\Notices::setFlash('error', __('It appears that you are accessing an invalid link or that your activation key has expired. If your account has not been activated in the last 48 hours, you will need to register again.'));
		\Response::redirect('admin/account/login');
	}

	public function action_forgot_password()
	{
		if (\Auth::has_access('maccess.user'))
		{
			\Response::redirect('admin');
		}

		if (\Input::post() && ! \Security::check_token())
		{
			\Notices::set('warning', __('The security token wasn\'t found. Try resubmitting.'));
		}
		elseif (\Input::post())
		{
			$val = \Validation::forge('forgotten_password');
			$val->add_field('email', __('Email'), 'required|trim|valid_email');

			if($val->run())
			{
				$input = $val->input();

				return static::send_change_password_email($input['email']);
			}
			else
			{
				\Notices::set('error', implode(' ', $val->error()));
			}
		}

		$this->_views['method_title'] = __('Forgot Password');
		$this->_views['main_content_view'] = \View::forge('foolz/foolframe::admin/account/forgot_password');

		return new Response(\View::forge('foolz/foolframe::admin/account', $this->_views));
	}

	public function action_change_password($id = null, $password_key = null)
	{
		if ($id != null && $password_key != null)
		{
			if (\Auth::check_new_password_key($id, $password_key))
			{
				if (\Input::post() && ! \Security::check_token())
				{
					\Notices::set('warning', __('The security token wasn\'t found. Try resubmitting.'));
				}
				elseif (\Input::post())
				{
					$val = \Validation::forge('forgotten_password');
					$val->add_field('password', __('Password'), 'required|min_length[4]|max_length[32]');
					$val->add_field('confirm_password', __('Confirm password'), 'required|match_field[password]');

					if($val->run())
					{
						$input = $val->input();

						try
						{
							\Auth::change_password($id, $password_key, $input['password']);
							\Response::redirect('admin/account/login');
						}
						catch (\Auth\FoolUserWrongKey $e)
						{
							\Notices::set('warning', __('It appears that you are trying to access an invalid link or your activation key has expired.'));
						}
					}
					else
					{
						\Notices::set('error', implode(' ', $val->error()));
					}
				}
				else
				{
					$this->_views['main_content_view'] = \View::forge('foolz/foolframe::admin/account/change_password');
				}
			}
			else
			{
				\Notices::set('warning', __('It appears that you are trying to access an invalid link or your activation key has expired.'));
			}

			$this->_views['method_title'] = __('Forgot Password');

			return new Response(\View::forge('foolz/foolframe::admin/default', $this->_views));
		}
		else
		{
			if ( ! \Auth::has_access('maccess.user'))
			{
				\Response::redirect('admin');
			}

			if (\Input::post() && ! \Security::check_token())
			{
				\Notices::set('warning', __('The security token wasn\'t found. Try resubmitting.'));
			}
			elseif (\Input::post())
			{
				return static::send_change_password_email(\Auth::get_email());
			}

			$this->_views['method_title'] = __('Change Password');
			$this->_views['main_content_view'] = \View::forge('foolz/foolframe::admin/account/request_change_password');

			return new Response(\View::forge('foolz/foolframe::admin/default', $this->_views));
		}
	}

	public function action_change_email($id = null, $email_key = null)
	{
		$this->_views['method_title'] = __('Change Email Address');

		if ( ! \Auth::has_access('maccess.user'))
		{
			\Response::redirect('admin/account/login');
		}

		if ($id != null && $email_key != null)
		{
			try
			{
				\Auth::change_email($id, $email_key);
				\Notices::setFlash('success', __('You have successfully verified your new email address.'));
				\Response::redirect();
			}
			catch (\Auth\FoolUserWrongKey $e)
			{
				\Notices::set('warning', __('It appears that you are accessing an invalid link or that your activation key has expired.'));
			}

			return new Response(\View::forge('foolz/foolframe::admin/default', $this->_views));
		}
		else
		{
			if (\Input::post() && ! \Security::check_token())
			{
				\Notices::set('warning', __('The security token wasn\'t found. Try resubmitting.'));
			}
			elseif (\Input::post())
			{
				$val = \Validation::forge('change_password');
				$val->add_field('password', __('Password'), 'required');
				$val->add_field('email', __('Email'), 'required|trim|valid_email');

				if($val->run())
				{
					$input = $val->input();

					try
					{
						$change_email_key = \Auth::create_change_email_key($input['email'], $input['password']);
					}
					catch (\Auth\FoolUserWrongPassword $e)
					{
						\Notices::setFlash('error', __('The password entered is incorrect.'));
						\Response::redirect('admin/account/change_email_request');
					}
					catch (\Auth\FoolUserEmailExists $e)
					{
						\Notices::setFlash('error', __('The email address is already associated with another username. Please use another email address.'));
						\Response::redirect('admin/account/change_email_request');
					}

					$user = \Users::getUser();

					$from = 'no-reply@'.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'no-email-assigned');

					$title = \Preferences::get('foolframe.gen.website_title').' '.__('Change Email Address');

					$content = \View::forge('foolz/foolframe::admin/account/email_email_change', array(
						'title' => $title,
						'site' => \Preferences::get('foolframe.gen.website_title'),
						'username' => $user->username,
						'link' => \Uri::create('admin/account/change_email/'.$user->id.'/'.$change_email_key)
					));

					\Package::load('email');
					$sendmail = \Email::forge();
					$sendmail->from($from, \Preferences::get('foolframe.gen.website_title'))
						->subject($title)
						->to($input['email'])
						->html_body(\View::forge('foolz/foolframe::email_default', array('title' => $title, 'content' => $content)));

					try
					{
						$sendmail->send();
						\Notices::setFlash('success', __('An email has been sent to verify your new email address. The activation link will only be valid for 24 hours.'));

					}
					catch(\EmailSendingFailedException $e)
					{
						// the email driver was unable to send the email. the account's email address will not be changed.
						\Notices::setFlash('error', __('An error was encountered and the system was unable to send the verification email. Please try again later.'));
						\Log::error(\Str::tr('The system was unable to send a verification email to :username. This user was attempting to change their email address.'), array('username' => $user->username));
					}

					\Response::redirect('admin/account/login');

				}
				else
				{
					\Notices::set('error', $val->error());
				}
			}

			$this->_views['main_content_view'] = \View::forge('foolz/foolframe::admin/account/request_change_email');

			return new Response(\View::forge('foolz/foolframe::admin/default', $this->_views));
		}

	}

	public function action_delete($id = null, $key = null)
	{
		$this->_views['method_title'] = __('Delete');

		if ($id != null && $key != null)
		{
			if ( ! \Auth::has_access('maccess.user'))
			{
				\Notices::set('warning', __('You must log in to delete your account with this verification link.'));

				return new Response(\View::forge('foolz/foolframe::admin/default', $this->_views));
			}

			try
			{
				\Auth::delete_account($id, $key);
				\Notices::set('success', __('Your account has been deleted from the system.'));
			}
			catch (\Auth\FoolUserWrongKey $e)
			{
				\Notices::set('warning', __('It appears that you are accessing an invalid link or your activation key has expired.'));
			}

			return new Response(\View::forge('foolz/foolframe::admin/default', $this->_views));
		}
		else
		{
			if ( ! \Auth::has_access('maccess.user'))
			{
				\Response::redirect('admin/account/login');
			}

			if (\Input::post() && ! \Security::check_token())
			{
				\Notices::set('warning', __('The security token wasn\'t found. Try resubmitting.'));
			}
			elseif (\Input::post())
			{
				$val = \Validation::forge('change_password');
				$val->add_field('password', __('Password'), 'required');

				if ($val->run())
				{
					$input = $val->input();

					try
					{
						$account_deletion_key = \Auth::create_account_deletion_key($input['password']);
					}
					catch (\Auth\FoolUserWrongPassword $e)
					{
						\Notices::setFlash('error', __('The password entered was incorrect.'));
						\Response::redirect('admin/account/delete');
					}

					$user = \Users::getUser();

					$from = 'no-reply@'.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'no-email-assigned');

					$title = \Preferences::get('foolframe.gen.website_title').' '.__('Account Deletion');

					$content = \View::forge('foolz/foolframe::admin/account/email_delete', array(
						'title' => $title,
						'site' => \Preferences::get('foolframe.gen.website_title'),
						'username' => $user->username,
						'link' => \Uri::create('admin/account/delete/'.$user->id.'/'.$account_deletion_key)
					));

					\Package::load('email');
					$sendmail = \Email::forge();
					$sendmail->from($from, \Preferences::get('foolframe.gen.website_title'))
						->subject($title)
						->to($user->email)
						->html_body(\View::forge('foolz/foolframe::email_default', array('title' => $title, 'content' => $content)));

					try
					{
						$sendmail->send();
						\Notices::setFlash('success', __('An email has been sent to verify the deletion of your account. The verification link will only work for 15 minutes.'));
					}
					catch(\EmailSendingFailedException $e)
					{
						// the email driver was unable to send the email. the account will not be deleted.
						\Notices::setFlash('error', __('An error was encountered and the system was unable to send the verification email. Please try again later.'));
						\Log::error(\Str::tr('The system was unable to send a verification email to :username. This user was attempting to delete their account.'), array('username' => $user->username));
					}

					\Response::redirect('admin/account/delete');
				}
				else
				{
					\Notices::set('error', implode(' ', $val->error()));
				}

			}

			$this->_views['main_content_view'] = \View::forge('foolz/foolframe::admin/account/request_delete');

			return new Response(\View::forge('foolz/foolframe::admin/default', $this->_views));
		}
	}

	public function send_change_password_email($email)
	{
		try
		{
			$password_key = \Auth::create_forgotten_password_key($email);
		}
		catch (\Auth\FoolUserWrongEmail $e)
		{
			\Notices::setFlash('error', __('The email address provided does not exist in the system. Please check and verify that it is correct.'));
			\Response::redirect('admin/account/forgotten_password');
		}

		$user = \Users::getUserBy('email', $email);

		$from = 'no-reply@'.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'no-email-assigned');

		$title = \Preferences::get('foolframe.gen.website_title').' '.__('New Password');

		$content = \View::forge('foolz/foolframe::admin/account/email_password_change', array(
			'title' => $title,
			'site' => \Preferences::get('foolframe.gen.website_title'),
			'username' => $user->username,
			'link' => \Uri::create('admin/account/change_password/'.$user->id.'/'.$password_key)
		));

		\Package::load('email');
		$sendmail = \Email::forge();
		$sendmail->from($from, \Preferences::get('foolframe.gen.website_title'))
			->subject($title)
			->to($email)
			->html_body(\View::forge('foolz/foolframe::email_default', array('title' => $title, 'content' => $content)));

		try
		{
			$sendmail->send();
			\Notices::setFlash('success', __('An email has been sent to verify that you wish to change your password. The verification link included will only work for the next 15 minutes.'));
		}
		catch(\EmailSendingFailedException $e)
		{
			// the email driver was unable to send the email. the account's password will not be changed..
			\Notices::setFlash('error', __('An error was encountered and the system was unable to send the verification email. Please try again later.'));
			\Log::error(\Str::tr('The system was unable to send a verification email to :username. This user was attempting to change their password.'), array('username' => $user->username));
		}

		\Auth::logout();
		\Response::redirect('admin/account/login');
	}

	public function action_profile()
	{
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
			'help' => '<img src="'.\Gravatar::get_gravatar(\Auth::get_email()).'" width="80" height="80" style="padding:2px; border: 1px solid #ccc;"/> '.
				\Str::tr(__('Your avatar is automatically fetched from :gravatar, based on your registration email.'),
				array('gravatar' => '<a href="http://gravatar.com" target="_blank">Gravatar</a>'))
		);

		$form['display_name'] = array(
			'type' => 'input',
			'database' => true,
			'label' => __('Display Name'),
			'class' => 'span3',
			'help' => __('Alternative name in place of login username'),
			'validation' => 'trim|max_length[32]'
		);

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

				\Notices::set('success', __('Your profile has been updated.'));

				\Auth::update_profile($result['success']);
			}
		}

		$data['object'] = (object) \Auth::get_profile();

		// generate profile form
		$this->_views["method_title"] = __('Profile');
		$this->_views["main_content_view"] = \View::forge('foolz/foolframe::admin/form_creator', $data);
		return new Response(\View::forge('foolz/foolframe::admin/default', $this->_views));
	}
}
