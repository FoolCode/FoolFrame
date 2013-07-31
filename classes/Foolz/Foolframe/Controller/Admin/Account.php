<?php

namespace Foolz\Foolframe\Controller\Admin;

use Foolz\Foolframe\Model\Notices;
use Foolz\Foolframe\Model\Validation\ActiveConstraint\Trim;
use Foolz\Foolframe\Model\Validation\Constraint\EqualsField;
use Foolz\Foolframe\Model\Validation\Validator;
use Swift_SendmailTransport;
use Swift_Mailer;
use Swift_Message;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Constraints as Assert;

class Account extends \Foolz\Foolframe\Controller\Admin
{
    public function before(Request $request)
    {
        parent::before($request);

        $this->param_manager->setParam('controller_title', _i('Account'));
    }

    public function action_login()
    {
        // redirect user to admin panel
        if (\Auth::has_access('maccess.user')) {
            \Response::redirect('admin');
        }

        $data = [];

        // the login button has been submitted - authenticate username and password
        if (\Input::post() && !\Security::check_token()) {
            \Notices::set('error', _i('The security token was not found. Please try again.'));
        } elseif (\Input::post()) {
            // load authentication instance
            $auth = \Auth::instance();

            // verify credentials
            try {
                $auth->login();
                \Response::redirect('admin');
            } catch (\Auth\FoolUserWrongUsernameOrPassword $e) {
                // invalid username or password was entered
                $data['username'] = \Input::post('username');
                \Notices::set('error', _i('You have entered an invalid username and/or password. Please try again.'));
            } catch (\Auth\FoolUserLimitExceeded $e) {
                // account has been locked due to excess authentication failures
                $data['username'] = \Input::post('username');
                \Notices::set('error', _i('After %d failed login attempts, this account has been locked. In order to unlock your account, please use the password reset system.', Config::get('foolz/foolframe', 'foolauth', 'attempts_to_lock')));
            }
        }

        // generate login form
        $this->param_manager->setParam('method_title', _i('Login'));
        $this->builder->createLayout('account');
        $this->builder->createPartial('body', 'account/login');
        return new Response($this->builder->build());
    }

    public function action_logout()
    {
        if (!\Security::check_token(\Input::get('token'))) {
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
        if (!\Security::check_token(\Input::get('token'))) {
            die('The security token didn\'t match or has expired.');
        }

        \Auth::logout(true);
        \Response::redirect('admin');
    }

    public function action_register()
    {
        if (\Auth::has_access('maccess.user')) {
            \Response::redirect('admin');
        }

        if (\Preferences::get('foolframe.auth.disable_registration')) {
            throw new NotFoundHttpException;
        }

        if (\Input::post() && !\Security::check_token()) {
            \Notices::set('warning', _i('The security token wasn\'t found. Try resubmitting.'));
        } elseif (\Input::post()) {

            $input = \Input::post();

            $recaptcha = ! \ReCaptcha::available()
                || \ReCaptcha::instance()->check_answer(
                    \Input::ip(), \Input::post('recaptcha_challenge_field'), \Input::post('recaptcha_response_field')
                );

            if (!$recaptcha) {
                \Notices::set('error', _i('The reCAPTCHA code entered does not match the one displayed.'));
            } else {
                $validator = new Validator();
                $validator
                    ->add('username', _i('Username'), [new Assert\NotBlank(), new Assert\Length(['min' => 4, 'max' => 32])])
                    ->add('email', _i('Email'), [new Assert\NotBlank(), new Assert\Email()])
                    ->add('password', _i('Password'), [new Assert\NotBlank(), new Assert\Length(['min' => 4, 'max' => 64])])
                    ->add('confirm_password', _i('Password'), [new EqualsField(_i('Password'), \Input::post('password'))])
                    ->validate($input);

                if(!$validator->getViolations()->count() && $input['password'] === $input['confirm_password']) {
                    try {
                        list($id, $activation_key) = \Auth::create_user($input['username'], $input['password'], $input['email']);
                    } catch (\Auth\FoolUserUpdateException $e) {
                        \Notices::setFlash('error', $e->getMessage());
                        \Response::redirect('admin/account/register');
                    }

                    // activate or send activation email
                    if (!$activation_key) {
                        \Notices::setFlash('success', _i('Congratulations! You have successfully registered.'));
                    } else {
                        $from = 'no-reply@'.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'no-email-assigned');

                        $title = \Preferences::get('foolframe.gen.website_title').' - '._i('Account Activation');

                        $this->builder->createLayout('email');
                        $this->builder->getProps()->setTitle([$title]);
                        $this->builder->createPartial('body', 'account/email/activation')
                            ->getParamManager()->setParams([
                                'title' => $title,
                                'site' => \Preferences::get('foolframe.gen.website_title'),
                                'username' => $input['username'],
                                'link' => \Uri::create('admin/account/activate/'.$id.'/'.$activation_key)
                            ]);

                        $message = Swift_Message::newInstance()
                            ->setFrom([$from => \Preferences::get('foolframe.gen.website_title')])
                            ->setTo($input['email'])
                            ->setSubject($title)
                            ->setBody($this->builder->build(), 'text/html');

                        $mailer = Swift_Mailer::newInstance(Swift_SendmailTransport::newInstance());
                        $result = $mailer->send($message);

                        if ($result != 1) {
                            // the email driver was unable to send the email. the account will be activated automatically.
                            \Auth::activate_user($id, $activation_key);
                            \Notices::setFlash('success', _i('Congratulations! You have successfully registered.'));
                            \Log::error('The system was unable to send an activation email to '.$input['username'].'. The account was activated automatically.');
                            \Response::redirect('admin/account/login');
                        }

                        \Notices::setFlash('success', _i('Congratulations! You have successfully registered. Please check your email to activate your account.'));
                    }

                    \Response::redirect('admin/account/login');
                } else {
                    Notices::set('error', $validator->getViolations()->getHtml());
                }
            }
        }

        $this->param_manager->setParam('method_title', _i('Register'));
        $this->builder->createLayout('account');
        $this->builder->createPartial('body', 'account/register');
        return new Response($this->builder->build());
    }

    public function action_activate($id, $activation_key)
    {
        if (\Auth::has_access('maccess.user')) {
            \Response::redirect('admin');
        }

        if (\Auth::activate_user($id, $activation_key)) {
            \Notices::setFlash('success', _i('Your account has been activated. You are now able to login and access the control panel.'));
            \Response::redirect('admin/account/login');
        }

        \Notices::setFlash('error', _i('It appears that you are accessing an invalid link or that your activation key has expired. If your account has not been activated in the last 48 hours, you will need to register again.'));
        \Response::redirect('admin/account/login');
    }

    public function action_forgot_password()
    {
        if (\Auth::has_access('maccess.user')) {
            \Response::redirect('admin');
        }

        if (\Input::post() && !\Security::check_token()) {
            \Notices::set('warning', _i('The security token wasn\'t found. Try resubmitting.'));
        } elseif (\Input::post()) {
            $validator = new Validator();
            $validator
                ->add('email', _i('Email'), [new Trim(), new Assert\NotBlank(), new Assert\Email()])
                ->validate(\Input::post());

            if(!$validator->getViolations()->count()) {
                $input = $validator->getFinalValues();

                return static::send_change_password_email($input['email']);
            } else {
                \Notices::set('error', implode(' ', $validator->getViolations()->getText()));
            }
        }

        $this->param_manager->setParam('method_title', _i('Forgot Password'));
        $this->builder->createLayout('account');
        $this->builder->createPartial('body', 'account/forgot_password');
        return new Response($this->builder->build());
    }

    public function action_change_password($id = null, $password_key = null)
    {
        if ($id !== null && $password_key !== null) {
            if (\Auth::check_new_password_key($id, $password_key)) {
                if (\Input::post() && !\Security::check_token()) {
                    \Notices::set('warning', _i('The security token wasn\'t found. Try resubmitting.'));
                } elseif (\Input::post()) {
                    $validator = new Validator();
                    $validator
                        ->add('password', _i('Password'), [new Assert\NotBlank(), new Assert\Length(['min' => 4, 'max' => 64])])
                        ->add('confirm_password', _i('Confirm Password'), [new EqualsField(_i('Password'), \Input::post('password'))])
                        ->validate(\Input::post());

                    if(!$validator->getViolations()->count()) {
                        $input = $validator->getFinalValues();

                        try {
                            \Auth::change_password($id, $password_key, $input['password']);
                            \Response::redirect('admin/account/login');
                        } catch (\Auth\FoolUserWrongKey $e) {
                            \Notices::set('warning', _i('It appears that you are trying to access an invalid link or your activation key has expired.'));
                        }
                    } else {
                        \Notices::set('error', implode(' ', $validator->getViolations()->getText()));
                    }
                } else {
                    $this->builder->createPartial('body', 'account/change_password');
                }
            } else {
                \Notices::set('warning', _i('It appears that you are trying to access an invalid link or your activation key has expired.'));
            }

            $this->_views['method_title'] = _i('Forgot Password');

            return new Response($this->builder->build());
        } else {
            if (!\Auth::has_access('maccess.user')) {
                \Response::redirect('admin');
            }

            if (\Input::post() && !\Security::check_token()) {
                \Notices::set('warning', _i('The security token wasn\'t found. Try resubmitting.'));
            } elseif (\Input::post()) {
                return static::send_change_password_email(\Auth::get_email());
            }

            $this->param_manager->setParam('method_title', _i('Change Password'));
            $this->builder->createPartial('body', 'account/request_change_password');
            return new Response($this->builder->build());
        }
    }

    public function action_change_email($id = null, $email_key = null)
    {
        $this->param_manager->setParam('method_title', _i('Change Email Address'));

        if (!\Auth::has_access('maccess.user')) {
            \Response::redirect('admin/account/login');
        }

        if ($id != null && $email_key != null) {
            try {
                \Auth::change_email($id, $email_key);
                \Notices::setFlash('success', _i('You have successfully verified your new email address.'));
                \Response::redirect();
            } catch (\Auth\FoolUserWrongKey $e) {
                \Notices::set('warning', _i('It appears that you are accessing an invalid link or that your activation key has expired.'));
            }

            return new Response($this->builder->build());
        } else {
            if (\Input::post() && !\Security::check_token()) {
                \Notices::set('warning', _i('The security token wasn\'t found. Try resubmitting.'));
            } elseif (\Input::post()) {
                $val = \Validation::forge('change_password');
                $val->add_field('password', _i('Password'), 'required');
                $val->add_field('email', _i('Email'), 'required|trim|valid_email');

                if($val->run()) {
                    $input = $val->input();

                    try {
                        $change_email_key = \Auth::create_change_email_key($input['email'], $input['password']);
                    } catch (\Auth\FoolUserWrongPassword $e) {
                        \Notices::setFlash('error', _i('The password entered is incorrect.'));
                        \Response::redirect('admin/account/change_email_request');
                    } catch (\Auth\FoolUserEmailExists $e) {
                        \Notices::setFlash('error', _i('The email address is already associated with another username. Please use another email address.'));
                        \Response::redirect('admin/account/change_email_request');
                    }

                    $user = \Users::getUser();

                    $from = 'no-reply@'.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'no-email-assigned');

                    $title = \Preferences::get('foolframe.gen.website_title').' '._i('Change Email Address');

                    $this->builder->createLayout('email');
                    $this->builder->getProps()->setTitle([$title]);
                    $this->builder->createPartial('body', 'account/email/email_change')
                        ->getParamManager()->setParams([
                            'title' => $title,
                            'site' => \Preferences::get('foolframe.gen.website_title'),
                            'username' => $user->username,
                            'link' => \Uri::create('admin/account/change_email/'.$user->id.'/'.$change_email_key)
                        ]);

                    $message = Swift_Message::newInstance()
                        ->setFrom([$from => \Preferences::get('foolframe.gen.website_title')])
                        ->setTo($input['email'])
                        ->setSubject($title)
                        ->setBody($this->builder->build(), 'text/html');

                    $mailer = Swift_Mailer::newInstance(Swift_SendmailTransport::newInstance());
                    $result = $mailer->send($message);

                    if ($result == 1) {
                        \Notices::setFlash('success', _i('An email has been sent to verify your new email address. The activation link will only be valid for 24 hours.'));
                    } else {
                        // the email driver was unable to send the email. the account's email address will not be changed.
                        \Notices::setFlash('error', _i('An error was encountered and the system was unable to send the verification email. Please try again later.'));
                        \Log::error('The system was unable to send a verification email to '.$user->username.'. This user was attempting to change their email address.');
                    }

                    \Response::redirect('admin/account/login');

                } else {
                    \Notices::set('error', $val->error());
                }
            }

            $this->builder->createPartial('body', 'account/request_change_email');
            return new Response($this->builder->build());
        }

    }

    public function action_delete($id = null, $key = null)
    {
        $this->_views['method_title'] = _i('Delete');

        if ($id !== null && $key !== null) {
            if (!\Auth::has_access('maccess.user')) {
                \Notices::set('warning', _i('You must log in to delete your account with this verification link.'));

                return new Response($this->builder->build());
            }

            try {
                \Auth::delete_account($id, $key);
                \Notices::set('success', _i('Your account has been deleted from the system.'));
            } catch (\Auth\FoolUserWrongKey $e) {
                \Notices::set('warning', _i('It appears that you are accessing an invalid link or your activation key has expired.'));
            }

            return new Response($this->builder->build());
        } else {
            if (!\Auth::has_access('maccess.user')) {
                \Response::redirect('admin/account/login');
            }

            if (\Input::post() && !\Security::check_token()) {
                \Notices::set('warning', _i('The security token wasn\'t found. Try resubmitting.'));
            } elseif (\Input::post()) {
                $val = \Validation::forge('change_password');
                $val->add_field('password', _i('Password'), 'required');

                if ($val->run()) {
                    $input = $val->input();

                    try {
                        $account_deletion_key = \Auth::create_account_deletion_key($input['password']);
                    } catch (\Auth\FoolUserWrongPassword $e) {
                        \Notices::setFlash('error', _i('The password entered was incorrect.'));
                        \Response::redirect('admin/account/delete');
                    }

                    $user = \Users::getUser();

                    $from = 'no-reply@'.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'no-email-assigned');

                    $title = \Preferences::get('foolframe.gen.website_title').' '._i('Account Deletion');

                    $this->builder->createLayout('email');
                    $this->builder->getProps()->setTitle([$title]);
                    $this->builder->createPartial('body', 'account/email/delete', [
                        'title' => $title,
                        'site' => \Preferences::get('foolframe.gen.website_title'),
                        'username' => $user->username,
                        'link' => \Uri::create('admin/account/delete/'.$user->id.'/'.$account_deletion_key)
                    ]);

                    $message = Swift_Message::newInstance()
                        ->setFrom([$from => \Preferences::get('foolframe.gen.website_title')])
                        ->setTo($input['email'])
                        ->setSubject($title)
                        ->setBody($this->builder->build(), 'text/html');

                    $mailer = Swift_Mailer::newInstance(Swift_SendmailTransport::newInstance());
                    $result = $mailer->send($message);

                    if ($result == 1) {
                        \Notices::setFlash('success', _i('An email has been sent to verify the deletion of your account. The verification link will only work for 15 minutes.'));
                    } else {
                        // the email driver was unable to send the email. the account will not be deleted.
                        \Notices::setFlash('error', _i('An error was encountered and the system was unable to send the verification email. Please try again later.'));
                        \Log::error('The system was unable to send a verification email to '.$user->username.'. This user was attempting to delete their account.');
                    }

                    \Response::redirect('admin/account/delete');
                } else {
                    \Notices::set('error', implode(' ', $val->error()));
                }

            }

            $this->builder->createPartial('body', 'account/request_delete');
            return new Response($this->builder->build());
        }
    }

    public function send_change_password_email($email)
    {
        try {
            $password_key = \Auth::create_forgotten_password_key($email);
        } catch (\Auth\FoolUserWrongEmail $e) {
            \Notices::setFlash('error', _i('The email address provided does not exist in the system. Please check and verify that it is correct.'));
            \Response::redirect('admin/account/forgotten_password');
        }

        $user = \Users::getUserBy('email', $email);

        $from = 'no-reply@'.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'no-email-assigned');

        $title = \Preferences::get('foolframe.gen.website_title').' '._i('New Password');

        $this->builder->createLayout('email');
        $this->builder->getProps()->setTitle([$title]);
        $this->builder->createPartial('body', 'account/email/password_change')
            ->getParamManager()->setParams([
                'title' => $title,
                'site' => \Preferences::get('foolframe.gen.website_title'),
                'username' => $user->username,
                'link' => \Uri::create('admin/account/change_password/'.$user->id.'/'.$password_key)
            ]);

        $message = Swift_Message::newInstance()
            ->setFrom([$from => \Preferences::get('foolframe.gen.website_title')])
            ->setTo($email)
            ->setSubject($title)
            ->setBody($this->builder->build(), 'text/html');

        $mailer = Swift_Mailer::newInstance(Swift_SendmailTransport::newInstance());
        $result = $mailer->send($message);

        if ($result == 1) {
            \Notices::setFlash('success', _i('An email has been sent to verify that you wish to change your password. The verification link included will only work for the next 15 minutes.'));
        } else {
            // the email driver was unable to send the email. the account's password will not be changed..
            \Notices::setFlash('error', _i('An error was encountered and the system was unable to send the verification email. Please try again later.'));
            \Log::error('The system was unable to send a verification email to '.$user->username.'. This user was attempting to change their password.');
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
            'help' => _i('You can customize your account here.')
        );

        $form['paragraph-2'] = array(
            'type' => 'paragraph',
            'help' => '<img src="'.\Gravatar::get_gravatar(\Auth::get_email()).'" width="80" height="80" style="padding:2px; border: 1px solid #ccc;"/> '.
                _i('Your avatar is automatically fetched from %s, based on your registration email.',
                '<a href="http://gravatar.com" target="_blank">Gravatar</a>')
        );

        $form['display_name'] = array(
            'type' => 'input',
            'database' => true,
            'label' => _i('Display Name'),
            'class' => 'span3',
            'help' => _i('Alternative name in place of login username'),
            'validation' => 'trim|max_length[32]'
        );

        $form['bio'] = array(
            'type' => 'textarea',
            'database' => true,
            'label' => 'Bio',
            'style' => 'height:150px;',
            'class' => 'span5',
            'help' => _i('Some details about you'),
            'validation' => 'trim|max_length[360]'
        );

        $form['twitter'] = array(
            'type' => 'input',
            'database' => true,
            'label' => 'Twitter',
            'class' => 'span3',
            'help' => _i('Your twitter nickname'),
            'validation' => 'trim|max_length[32]'
        );

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
            $result = \Validation::form_validate($form);

            if (isset($result['error'])) {
                \Notices::set('warning', $result['error']);
            } else {
                if (isset($result['warning'])) {
                    \Notices::set('warning', $result['warning']);
                }

                \Notices::set('success', _i('Your profile has been updated.'));

                \Auth::update_profile($result['success']);
            }
        }

        $data['object'] = (object) \Auth::get_profile();

        // generate profile form
        $this->param_manager->setParam('method_title', _i('Profile'));
        $this->builder->createPartial('body', 'form_creator')->getParamManager()->setParams($data);
        return new Response($this->builder->build());
    }
}
