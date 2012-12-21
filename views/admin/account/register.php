<div class="well">

	<?= \Form::open(array('onsubmit' => 'fuel_set_csrf_token(this);')) ?>
	<?= \Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token()); ?>

	<label><?= \Form::label(__('Username'), 'username') ?></label>
	<?= Form::input(array(
		'name' => 'username',
		'id' => 'username',
		'value' => \Input::post('username'),
		'maxlength' => 32,
		'size' => 30,
		'placeholder' => __('required')
	)) ?>

	<label><?= \Form::label(__('Email Address'), 'email') ?></label>
	<?= \Form::input(array(
		'name' => 'email',
		'id' => 'email',
		'value' => \Input::post('email'),
		'maxlength' => 80,
		'size' => 30,
		'placeholder' => __('required')
	)) ?>


	<label><?= \Form::label(__('Password'), 'password') ?></label>
	<?= \Form::password(array(
		'name' => 'password',
		'id' => 'password',
		'value' => \Input::post('password'),
		'maxlength' => 32,
		'size' => 30,
		'placeholder' => __('required')
	)) ?>


	<label><?= \Form::label(__('Confirm Password'), 'confirm_password') ?></label>
	<?= \Form::password(array(
		'name' => 'confirm_password',
		'id' => 'confirm_password',
		'value' => \Input::post('confirm_password'),
		'maxlength' => 32,
		'size' => 30,
		'placeholder' => __('required')
	)) ?>


	<?= (\ReCaptcha::available()) ? '<br/><br/>'.\ReCaptcha::instance()->get_html() : '' ?>


	<br/><br/>

	<?= \Form::submit(array(
		'name' => 'register',
		'value' => __('Register'),
		'class' => 'btn btn-primary'
	)) ?>


	<input type="button" onClick="window.location.href='<?= \Uri::create('/admin/account/login/') ?>'" class="btn" value="<?= htmlspecialchars(__("Back to login")) ?>" />

	<?= \Form::close() ?>

</div>