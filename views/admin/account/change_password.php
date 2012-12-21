<div class="well">

	<p>
		<?= __('Insert the new password.') ?>
	</p>

	<?= \Form::open(array('onsubmit' => 'fuel_set_csrf_token(this);')) ?>
	<?= \Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token()); ?>

	<label><?= \Form::label(__('Password'), 'password') ?></label>
	<?= \Form::password(array(
		'name' => 'password',
		'id' => 'password',
		'value' => \Input::post('password'),
		'placeholder' => __('Required')
	)) ?>

	<label><?= \Form::label(__('Confirm Password'), 'confirm_password') ?></label>
	<?= \Form::password(array(
		'name' => 'confirm_password',
		'id' => 'confirm_password',
		'value' => \Input::post('confirm_password'),
		'placeholder' => __('Required')
	)) ?>

	<br/>

	<?= \Form::submit(array('name' => 'submit', 'value' => __('Login'), 'class' => 'btn btn-primary')) ?>

	<?= \Form::close() ?>

</div>