<div class="admin-container">
	<div class="admin-container-header"><?= __('Change Password') ?></div>
	<p>
		<?= __('If you wish to change your current password, a message will be sent to the email address associated with your account. It will provide you with an URL to change your password and verify your identity.') ?>

		<hr/>

		<?= \Form::open(['onsubmit' => 'fuel_set_csrf_token(this);']) ?>
		<?= \Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token()) ?>
		<?= \Form::submit(['class' => 'btn btn-primary', 'name' => 'submit', 'value' => __('Request Password Change')]) ?>
		<?= \Form::close() ?>
	</p>
</div>