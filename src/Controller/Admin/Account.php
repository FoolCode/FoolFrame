<?php

namespace Foolz\FoolFrame\Controller\Admin;

use Foolz\FoolFrame\Model\Auth;
use Foolz\FoolFrame\Model\Auth\WrongUsernameOrPasswordException;
use Foolz\FoolFrame\Model\Auth\AccountNotVerifiedException;
use Foolz\FoolFrame\Model\Cookie;
use Foolz\FoolFrame\Model\Validation\ActiveConstraint\Trim;
use Foolz\FoolFrame\Model\Validation\Constraint\EqualsField;
use Foolz\FoolFrame\Model\Validation\Validator;
use Foolz\Inet\Inet;
use forxer\Gravatar\Gravatar;
use Neutron\ReCaptcha\ReCaptcha;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Constraints as Assert;

class Account extends \Foolz\FoolFrame\Controller\Admin
{
    /**
     * @var Auth
     */
    protected $auth;

    public function before()
    {
        parent::before();
        $this->param_manager->setParam('controller_title', _i('Account'));
    }

    public function action_login()
    {
        // redirect user to admin panel
        if ($this->getAuth()->hasAccess('maccess.user')) {
            return $this->redirectToAdmin();
        }

        // the login button has been submitted - authenticate username and password
        if ($this->getPost() && !$this->security->checkCsrfToken($this->getRequest())) {
            $this->notices->set('error', _i('The security token was not found. Please try again.'));
        } elseif ($this->getPost()) {
            // load authentication instance

            // verify credentials
            try {
                $this->getAuth()->authenticateWithUsernameAndPassword(
                    $this->getPost('username', ''),
                    $this->getPost('password', '')
                );

                $rememberme_token = $this->getAuth()->createAutologinHash(
                    Inet::ptod($this->getRequest()->getClientIp()),
                    $this->getRequest()->headers->get('User-Agent')
                );

                $response = new RedirectResponse($this->uri->create('admin'));
                //$this->getRequest()->getSession()->set('rememberme', $rememberme_token);
                if ($this->getPost('remember')) {
                    $response->headers->setCookie(new Cookie($this->getContext(), 'rememberme', $rememberme_token, 365 * 24 * 60 * 60));
                }

                return $response;
            } catch (WrongUsernameOrPasswordException $e) {
                $this->notices->set('error', _i('You have entered an invalid username and/or password. Please try again.'));
            } catch (AccountNotVerifiedException $e) {
                $this->notices->set('error', _i('Your account has not been activated yet, please check your email.'));
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
        if (!$this->getAuth()->hasAccess('maccess.user')) {
            return $this->redirectToLogin();
        }

        if (!$this->checkCsrfTokenGet()) {
            die('The security token is invalid.');
        }

        $response = new RedirectResponse($this->uri->create('admin/account/login'));
        $response->headers->clearCookie($this->config->get('foolz/foolframe', 'config', 'config.cookie_prefix').'rememberme');
        //$this->getRequest()->getSession()->clear();

        return $response;
    }

    public function action_register()
    {
        if ($this->getAuth()->hasAccess('maccess.user')) {
            return $this->redirectToAdmin();
        }

        if ($this->preferences->get('foolframe.auth.disable_registration')) {
            throw new NotFoundHttpException;
        }

        if ($this->getPost() && !$this->security->checkCsrfToken($this->getRequest())) {
            $this->notices->set('warning', _i('The security token wasn\'t found. Try resubmitting.'));
        } elseif ($this->getPost()) {

            $input = $this->getPost();

            $recaptcha = true;
            if ($this->preferences->get('foolframe.auth.recaptcha_public', false)) {

                $recaptcha_obj = ReCaptcha::create($this->preferences->get('foolframe.auth.recaptcha_public'), $this->preferences->get('foolframe.auth.recaptcha_private'));
                $recaptcha_result = $recaptcha_obj->checkAnswer(
                    $this->getRequest()->getClientIp(),
                    $this->getPost('recaptcha_challenge_field'),
                    $this->getPost('recaptcha_response_field')
                );

                if (!$recaptcha_result->isValid()) {
                    $recaptcha = false;
                }
            }

            if (!$recaptcha) {
                $this->notices->set('error', _i('The reCAPTCHA code entered does not match the one displayed.'));
            } else {
                $validator = new Validator();
                $validator
                    ->add('username', _i('Username'), [new Assert\NotBlank(), new Assert\Length(['min' => 4, 'max' => 32])])
                    ->add('email', _i('Email'), [new Assert\NotBlank(), new Assert\Email()])
                    ->add('password', _i('Password'), [new Assert\NotBlank(), new Assert\Length(['min' => 4, 'max' => 64])])
                    ->add('confirm_password', _i('Confirm Password'), [new EqualsField(['field' => _i('Password'), 'value' => $this->getPost('password')])])
                    ->validate($input);

                if(!$validator->getViolations()->count() && $input['password'] === $input['confirm_password']) {
                    try {
                        list($id, $activation_key) = $this->getAuth()->createUser($input['username'], $input['password'], $input['email']);
                    } catch (Auth\UpdateException $e) {
                        $this->notices->setFlash('error', $e->getMessage());
                        return $this->redirect('admin/account/register');
                    }

                    // activate or send activation email
                    if (!$activation_key) {
                        $this->notices->setFlash('success', _i('Congratulations! You have successfully registered.'));
                    } else {
                        $from = 'no-reply@'.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'no-email-assigned');

                        $title = $this->preferences->get('foolframe.gen.website_title').' - '._i('Account Activation');

                        $this->builder->createLayout('email');
                        $this->builder->getProps()->setTitle([$title]);
                        $this->builder->createPartial('body', 'account/email/activation')
                            ->getParamManager()->setParams([
                                'title' => $title,
                                'site' => $this->preferences->get('foolframe.gen.website_title'),
                                'username' => $input['username'],
                                'link' => $this->uri->create('admin/account/activate/'.$id.'/'.$activation_key)
                            ]);

                        $message = $this->mailer->create()
                            ->setFrom([$from => $this->preferences->get('foolframe.gen.website_title')])
                            ->setTo($input['email'])
                            ->setSubject($title)
                            ->setBody($this->builder->build(), 'text/html');

                        if ($this->mailer->send($message) !== 1) {
                            // the email driver was unable to send the email. the account will be activated automatically.
                            $this->getAuth()->activateUser($id, $activation_key);
                            $this->notices->setFlash('success', _i('Congratulations! You have successfully registered.'));
                            $this->getContext()
                                ->getService('logger')
                                ->error('The system was unable to send an activation email to '.$input['username'].'. The account was activated automatically.');

                            return $this->redirect('admin/account/login');
                        }

                        $this->notices->setFlash('success', _i('Congratulations! You have successfully registered. Please check your email to activate your account.'));
                    }

                    return $this->redirectToLogin();
                } else {
                    $this->notices->set('error', $validator->getViolations()->getHtml());
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
        if ($this->getAuth()->hasAccess('maccess.user')) {
            return $this->redirectToAdmin();
        }

        if ($this->getAuth()->activateUser($id, $activation_key)) {
            $this->notices->setFlash('success', _i('Your account has been activated. You are now able to login and access the control panel.'));
            return $this->redirectToLogin();
        }

        $this->notices->setFlash('error', _i('It appears that you are accessing an invalid link or that your activation key has expired. If your account has not been activated in the last 48 hours, you will need to register again.'));
        return $this->redirectToLogin();
    }

    public function action_forgot_password()
    {
        if ($this->getAuth()->hasAccess('maccess.user')) {
            return $this->redirectToAdmin();
        }

        if ($this->getPost() && !$this->security->checkCsrfToken($this->getRequest())) {
            $this->notices->set('warning', _i('The security token wasn\'t found. Try resubmitting.'));
        } elseif ($this->getPost()) {
            $validator = new Validator();
            $validator
                ->add('email', _i('Email'), [new Trim(), new Assert\NotBlank(), new Assert\Email()])
                ->validate($this->getPost());

            if(!$validator->getViolations()->count()) {
                $input = $validator->getFinalValues();

                return $this->sendChangePasswordEmail($input['email']);
            } else {
                $this->notices->set('error', $validator->getViolations()->getText());
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
            if ($this->getAuth()->checkNewPasswordKey($id, $password_key)) {
                if ($this->getPost() && !$this->security->checkCsrfToken($this->getRequest())) {
                    $this->notices->set('warning', _i('The security token wasn\'t found. Try resubmitting.'));
                } elseif ($this->getPost()) {
                    $validator = new Validator();
                    $validator
                        ->add('password', _i('Password'), [new Assert\NotBlank(), new Assert\Length(['min' => 4, 'max' => 64])])
                        ->add('confirm_password', _i('Confirm Password'), [new EqualsField(['field' => _i('Password'), 'value' => $this->getPost('password')])])
                        ->validate($this->getPost());

                    if(!$validator->getViolations()->count()) {
                        $input = $validator->getFinalValues();

                        try {
                            $this->getAuth()->changePassword($id, $password_key, $input['password']);
                            return $this->redirectToLogin();
                        } catch (Auth\WrongKeyException $e) {
                            $this->notices->set('warning', _i('It appears that you are trying to access an invalid link or your activation key has expired.'));
                        }
                    } else {
                        $this->notices->set('error', $validator->getViolations()->getText());
                    }
                } else {
                    $this->builder->createPartial('body', 'account/change_password');
                }
            } else {
                $this->notices->set('warning', _i('It appears that you are trying to access an invalid link or your activation key has expired.'));
            }

            $this->_views['method_title'] = _i('Forgot Password');

            return new Response($this->builder->build());
        } else {
            if (!$this->getAuth()->hasAccess('maccess.user')) {
                return $this->redirectToAdmin();
            }

            if ($this->getPost() && !$this->security->checkCsrfToken($this->getRequest())) {
                $this->notices->set('warning', _i('The security token wasn\'t found. Try resubmitting.'));
            } elseif ($this->getPost()) {
                return $this->sendChangePasswordEmail($this->getAuth()->getUser()->getEmail());
            }

            $this->param_manager->setParam('method_title', _i('Change Password'));
            $this->builder->createPartial('body', 'account/request_change_password');
            return new Response($this->builder->build());
        }
    }

    public function action_change_email($id = null, $email_key = null)
    {
        $this->param_manager->setParam('method_title', _i('Change Email Address'));

        if (!$this->getAuth()->hasAccess('maccess.user')) {
            return $this->redirectToLogin();
        }

        if ($id !== null && $email_key !== null) {
            try {
                $this->getAuth()->changeEmail($id, $email_key);
                $this->notices->setFlash('success', _i('You have successfully verified your new email address.'));
                return $this->redirect('admin/account/change_email');
            } catch (Auth\WrongKeyException $e) {
                $this->notices->set('warning', _i('It appears that you are accessing an invalid link or that your activation key has expired.'));
            }

            return new Response($this->builder->build());
        } else {
            if ($this->getPost() && !$this->security->checkCsrfToken($this->getRequest())) {
                $this->notices->set('warning', _i('The security token wasn\'t found. Try resubmitting.'));
            } elseif ($this->getPost()) {
                $validator = new Validator();
                $validator
                    ->add('email', _i('Email'), [new Trim(), new Assert\NotBlank(), new Assert\Email()])
                    ->add('password', _i('Password'), [new Assert\NotBlank()])
                    ->validate($this->getPost());

                if(!$validator->getViolations()->count()) {
                    $input = $validator->getFinalValues();

                    try {
                        $change_email_key = $this->getAuth()->createChangeEmailKey($input['email'], $input['password']);
                    } catch (Auth\WrongPasswordException $e) {
                        $this->notices->setFlash('error', _i('The password entered is incorrect.'));
                        return $this->redirect('admin/account/change_email');
                    } catch (Auth\EmailExistsException $e) {
                        $this->notices->setFlash('error', _i('The email address is already associated with another username. Please use another email address.'));
                        return $this->redirect('admin/account/change_email');
                    }

                    $user = $this->getAuth()->getUser();

                    $from = 'no-reply@'.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'no-email-assigned');

                    $title = $this->preferences->get('foolframe.gen.website_title').' '._i('Change Email Address');

                    $this->builder->createLayout('email');
                    $this->builder->getProps()->setTitle([$title]);
                    $this->builder->createPartial('body', 'account/email/email_change')
                        ->getParamManager()->setParams([
                            'title' => $title,
                            'site' => $this->preferences->get('foolframe.gen.website_title'),
                            'username' => $user->username,
                            'link' => $this->uri->create('admin/account/change_email/'.$user->id.'/'.$change_email_key)
                        ]);

                    $message = $this->mailer->create()
                        ->setFrom([$from => $this->preferences->get('foolframe.gen.website_title')])
                        ->setTo($user->email)
                        ->setSubject($title)
                        ->setBody($this->builder->build(), 'text/html');

                    if ($this->mailer->send($message) === 1) {
                        $this->notices->setFlash('success', _i('An email has been sent to verify your new email address. The activation link will only be valid for 24 hours.'));
                    } else {
                        // the email driver was unable to send the email. the account's email address will not be changed.
                        $this->notices->setFlash('error', _i('An error was encountered and the system was unable to send the verification email. Please try again later.'));
                        $this->getContext()->getService('logger')->error('The system was unable to send a verification email to '.$user->username.'. This user was attempting to change their email address.');
                    }

                    return $this->redirect('admin/account/login');

                } else {
                    $this->notices->set('error', $validator->getViolations()->getText());
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
            if (!$this->getAuth()->hasAccess('maccess.user')) {
                $this->notices->set('warning', _i('You must log in to delete your account with this verification link.'));

                return new Response($this->builder->build());
            }

            try {
                $this->getAuth()->deleteAccount($id, $key);
                $this->notices->set('success', _i('Your account has been deleted from the system.'));
            } catch (Auth\WrongKeyException $e) {
                $this->notices->set('warning', _i('It appears that you are accessing an invalid link or your activation key has expired.'));
            }

            return new Response($this->builder->build());
        } else {
            if (!$this->getAuth()->hasAccess('maccess.user')) {
                return $this->redirect('admin/account/login');
            }

            if ($this->getPost() && !$this->security->checkCsrfToken($this->getRequest())) {
                $this->notices->set('warning', _i('The security token wasn\'t found. Try resubmitting.'));
            } elseif ($this->getPost()) {
                $validator = new Validator();
                $validator
                    ->add('password', _i('Password'), [new Assert\NotBlank()])
                    ->validate($this->getPost());

                if (!$validator->getViolations()->count()) {
                    $input = $validator->getFinalValues();

                    try {
                        $account_deletion_key = $this->getAuth()->createAccountDeletionKey($input['password']);
                    } catch (Auth\WrongPasswordException $e) {
                        $this->notices->setFlash('error', _i('The password entered was incorrect.'));
                        return $this->redirect('admin/account/delete');
                    }

                    $user = $this->getAuth()->getUser();

                    $from = 'no-reply@'.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'no-email-assigned');

                    $title = $this->preferences->get('foolframe.gen.website_title').' '._i('Account Deletion');

                    $this->builder->createLayout('email');
                    $this->builder->getProps()->setTitle([$title]);
                    $this->builder->createPartial('body', 'account/email/delete_account')
                        ->getParamManager()->setParams([
                            'title' => $title,
                            'site' => $this->preferences->get('foolframe.gen.website_title'),
                            'username' => $user->username,
                            'link' => $this->uri->create('admin/account/delete/'.$user->id.'/'.$account_deletion_key)
                        ]);

                    $message = $this->mailer->create()
                        ->setFrom([$from => $this->preferences->get('foolframe.gen.website_title')])
                        ->setTo($user->email)
                        ->setSubject($title)
                        ->setBody($this->builder->build(), 'text/html');

                    if ($this->mailer->send($message) === 1) {
                        $this->notices->setFlash('success', _i('An email has been sent to verify the deletion of your account. The verification link will only work for 15 minutes.'));
                    } else {
                        // the email driver was unable to send the email. the account will not be deleted.
                        $this->notices->setFlash('error', _i('An error was encountered and the system was unable to send the verification email. Please try again later.'));
                        $this->getContext()->getService('logger')->error('The system was unable to send a verification email to '.$user->username.'. This user was attempting to delete their account.');
                    }

                    return $this->redirect('admin/account/delete');
                } else {
                    $this->notices->set('error', $validator->getViolations()->getText());
                }

            }

            $this->builder->createPartial('body', 'account/request_delete');
            return new Response($this->builder->build());
        }
    }

    public function sendChangePasswordEmail($email)
    {
        try {
            $password_key = $this->getAuth()->createForgottenPasswordKey($email);
        } catch (Auth\WrongEmailException $e) {
            $this->notices->setFlash('error', _i('The email address provided does not exist in the system. Please check and verify that it is correct.'));
            return $this->redirect('admin/account/forgotten_password');
        }

        /** @var \Foolz\FoolFrame\Model\Users $users */
        $users = $this->getContext()->getService('users');
        $user = $users->getUserBy('email', $email);

        $from = 'no-reply@'.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'no-email-assigned');

        $title = $this->preferences->get('foolframe.gen.website_title').' '._i('New Password');

        $this->builder->createLayout('email');
        $this->builder->getProps()->setTitle([$title]);
        $this->builder->createPartial('body', 'account/email/password_change')
            ->getParamManager()->setParams([
                'title' => $title,
                'site' => $this->preferences->get('foolframe.gen.website_title'),
                'username' => $user->username,
                'link' => $this->uri->create('admin/account/change_password/'.$user->id.'/'.$password_key)
            ]);

        $message = $this->mailer->create()
            ->setFrom([$from => $this->preferences->get('foolframe.gen.website_title')])
            ->setTo($email)
            ->setSubject($title)
            ->setBody($this->builder->build(), 'text/html');

        if ($this->mailer->send($message) === 1) {
            $this->notices->setFlash('success', _i('An email has been sent to verify that you wish to change your password. The verification link included will only work for the next 15 minutes.'));
        } else {
            // the email driver was unable to send the email. the account's password will not be changed..
            $this->notices->setFlash('error', _i('An error was encountered and the system was unable to send the verification email. Please try again later.'));
            $this->getContext()->getService('logger')->error('The system was unable to send a verification email to '.$user->username.'. This user was attempting to change their password.');
        }

        return $this->action_logout();
    }

    public function action_profile()
    {
        if (!$this->getAuth()->hasAccess('maccess.user')) {
            return $this->redirectToLogin();
        }

        $data = [];
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
            'help' => '<img src="'.Gravatar::image($this->getAuth()->getUser()->getEmail()).'" width="80" height="80" style="padding:2px; border: 1px solid #ccc;"/> '.
                _i('Your avatar is automatically fetched from %s, based on your registration email.',
                '<a href="http://gravatar.com" target="_blank">Gravatar</a>')
        );

        $form['display_name'] = array(
            'type' => 'input',
            'database' => true,
            'label' => _i('Display Name'),
            'class' => 'span3',
            'help' => _i('Alternative name in place of login username'),
            'validation' => [new Trim(), new Assert\Length(['max' => 32])]
        );

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

        $form['submit'] = array(
            'type' => 'submit',
            'class' => 'btn btn-primary',
            'value' => _i('Submit')
        );

        $form['close'] = array(
            'type' => 'close'
        );

        $data['form'] = $form;

        if ($this->getPost() && !$this->security->checkCsrfToken($this->getRequest())) {
            $this->notices->set('warning', _i('The security token wasn\'t found. Try resubmitting.'));
        } elseif ($this->getPost()) {
            $result = Validator::formValidate($form, $this->getPost());

            if (isset($result['error'])) {
                $this->notices->set('warning', $result['error']);
            } else {
                if (isset($result['warning'])) {
                    $this->notices->set('warning', $result['warning']);
                }

                $this->notices->set('success', _i('Your profile has been updated.'));

                $user = $this->getAuth()->getUser();
                $user->save($result['success']);
            }
        }

        $data['object'] = (object) $this->getAuth()->getUser();

        // generate profile form
        $this->param_manager->setParam('method_title', _i('Profile'));
        $this->builder->createPartial('body', 'form_creator')->getParamManager()->setParams($data);
        return new Response($this->builder->build());
    }
}
