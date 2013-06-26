<?php

namespace Foolz\Foolframe\Theme\Admin\Partial\Account;

class RequestChangePassword extends \Foolz\Theme\View
{
	public function toString()
	{ ?>
<div class="admin-container">
	<div class="admin-container-header"><?= _i('Change Password') ?></div>
	<p>
		<?= _i('If you would like to change your current password, a message containing a password reset link will be sent to the email address associated with your account.') ?>

		<hr/>

		<?= \Form::open(['onsubmit' => 'fuel_set_csrf_token(this);']) ?>
		<?= \Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token()) ?>
		<?= \Form::submit(['class' => 'btn btn-primary', 'name' => 'submit', 'value' => _i('Request Password Change')]) ?>
		<?= \Form::close() ?>
	</p>
</div>
<?php
	}
}