<div class="well">

	<p>
		<?= __('Enter the new email address you wish to associate with your account. You must enter your password and complete the email verification process to confirm this change being made.') ?>
	</p>

	<?= \Form::open(array('onsubmit' => 'fuel_set_csrf_token(this);')) ?>
	<?= \Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token()); ?>

	<label><?= \Form::label(__('New Email Address'), 'email') ?></label>
	<?= \Form::input(array(
		'name' => 'email',
		'id' => 'email',
		'value' => \Input::post('email'),
		'placeholder' => __('Required')
	)) ?>

	<label><?= \Form::label(__('Password'), 'password') ?></label>
	<?= \Form::password(array(
		'name' => 'password',
		'id' => 'password',
		'value' => \Input::post('password'),
		'placeholder' => __('Required')
	)) ?>

	<br/>

	<?= \Form::submit(array('name' => 'submit', 'value' => __('Submit'), 'class' => 'btn btn-primary')) ?>

	<?= \Form::close() ?>

</div>