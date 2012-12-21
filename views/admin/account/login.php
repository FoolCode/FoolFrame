<div class="well">
	<?= \Form::open(array('onsubmit' => 'fuel_set_csrf_token(this);')) ?>
	<?= \Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token()); ?>

	<label><?= \Form::label(__('Username'), 'username') ?></label>
	<?= \Form::input(array(
		'name' => 'username',
		'id' => 'username',
		'value' => \Input::post('username'),
		'maxlength' => 80,
		'size' => 30,
		'placeholder' => __('Required')
	)) ?>

	<label><?= \Form::label(__('Password'), 'password') ?></label>
	<?= \Form::password(array(
		'name' => 'password',
		'id' => 'password',
		'size' => 30,
		'placeholder' => __('Required')
	)) ?>


	<label class="checkbox">
	<?= \Form::checkbox(array(
		'name' => 'remember',
		'id' => 'remember',
		'value' => 1,
		'checked' => \Input::post('remember'),
	)) ?>
	<?= \Form::label(__('Remember Me'), 'remember') ?>
	</label>

	<?= \Form::submit(array('name' => 'submit', 'value' => __('Login'), 'class' => 'btn btn-primary')) ?>

	<input type="button" onClick="window.location.href='<?= \Uri::create('/admin/account/forgot_password/') ?>'" class="btn" value="<?= htmlspecialchars(__("Forgot Password")) ?>" />
	<?php if ( ! \Preferences::get('ff.auth.disable_registration')) : ?>
		<input type="button" onClick="window.location.href='<?= \Uri::create('/admin/account/register/') ?>'" class="btn" value="<?= htmlspecialchars(__("Register")) ?>" />
	<?php endif; ?>

	<?= \Form::close() ?>

</div>