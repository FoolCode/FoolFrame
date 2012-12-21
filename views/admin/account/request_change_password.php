<div class="well">

	<p>
		<?= __('If you wish to change your current password, an email will be sent to the email address associated with your account providing you with a link to securely change your password.') ?>
	</p>

	<?= \Form::open(array('onsubmit' => 'fuel_set_csrf_token(this);')) ?>
	<?= \Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token()); ?>

	<?= \Form::submit(array('name' => 'submit', 'value' => __('Request Password Change'), 'class' => 'btn btn-primary')) ?>

	<?= \Form::close() ?>

</div>