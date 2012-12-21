<div class="well">

	<p>
		<?= __('If you wish to delete your account, an email will be sent to email address associated with your account providing you with a link to securely delete your account.') ?>
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

	<br/>

	<?= Form::submit(array('name' => 'submit', 'value' => __('Request Account Deletion'), 'class' => 'btn btn-primary')) ?>

	<?= Form::close() ?>

</div>