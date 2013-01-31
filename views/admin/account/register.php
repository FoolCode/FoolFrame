<?= \Form::open(['class' => 'form-account', 'onsubmit' => 'fuel_set_csrf_token(this);']) ?>
	<?= \Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token()); ?>
	<h2 class="form-account-heading"><?= __('Register') ?></h2>

	<?= \Form::input([
		'class' => 'input-block-level',
		'name' => 'username',
		'value' => \Input::post('username'),
		'placeholder' => __('Username'),
		'required' => true
	]) ?>

	<?= \Form::input([
		'class' => 'input-block-level',
		'name' => 'email',
		'type' => 'email',
		'value' => \Input::post('email'),
		'placeholder' => __('E-mail Address'),
		'required' => true
	]) ?>

	<?= \Form::password([
		'class' => 'input-block-level',
		'name' => 'password',
		'placeholder' => __('Password'),
		'required' => true
	]) ?>

	<?= \Form::password([
		'class' => 'input-block-level',
		'name' => 'confirm_password',
		'placeholder' => __('Confirm Password'),
		'required' => true
	]) ?>

	<?= (\ReCaptcha::available()) ? '<br/><br/>'.\ReCaptcha::instance()->get_html() : '' ?>

	<?= Form::submit(['class' => 'btn btn-primary', 'name' => 'register', 'value' => __('Register')]) ?>

	<input type="button" class="btn" onClick="window.location.href='<?= \Uri::create('/admin/account/forgot_password/') ?>'" value="<?= htmlspecialchars(__('Forgot Password')) ?>" />
	<input type="button" onClick="window.location.href='<?= \Uri::create('/admin/account/login/') ?>'" class="btn" value="<?= htmlspecialchars(__('Back')) ?>" />
<?= \Form::close() ?>