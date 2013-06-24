<?php

namespace Foolz\Foolframe\Theme\Admin\Partial\Account;

class Register extends \Foolz\Theme\View
{
	public function toString()
	{ ?>
	<?= \Form::open(['class' => 'form-account', 'onsubmit' => 'fuel_set_csrf_token(this);']) ?>
	<?= \Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token()); ?>
	<h2 class="form-account-heading"><?= _i('Register') ?></h2>

	<?= \Form::input([
		'class' => 'input-block-level',
		'name' => 'username',
		'value' => \Input::post('username'),
		'placeholder' => _i('Username'),
		'required' => true
	]) ?>

	<?= \Form::input([
		'class' => 'input-block-level',
		'name' => 'email',
		'type' => 'email',
		'value' => \Input::post('email'),
		'placeholder' => _i('Email Address'),
		'required' => true
	]) ?>

	<?= \Form::password([
		'class' => 'input-block-level',
		'name' => 'password',
		'placeholder' => _i('Password'),
		'required' => true
	]) ?>

	<?= \Form::password([
		'class' => 'input-block-level',
		'name' => 'confirm_password',
		'placeholder' => _i('Confirm Password'),
		'required' => true
	]) ?>

	<?= (\ReCaptcha::available()) ? '<br/><br/>'.\ReCaptcha::instance()->get_html() : '' ?>

	<?= \Form::submit(['class' => 'btn btn-primary', 'name' => 'register', 'value' => _i('Register')]) ?>

	<input type="button" class="btn" onClick="window.location.href='<?= \Uri::create('/admin/account/forgot_password/') ?>'" value="<?= htmlspecialchars(_i('Forgot Password')) ?>" />
	<input type="button" onClick="window.location.href='<?= \Uri::create('/admin/account/login/') ?>'" class="btn" value="<?= htmlspecialchars(_i('Back')) ?>" />
<?= \Form::close() ?>
<?php
	}
}