<div class="admin-container">
	<div class="admin-container-header"><?= __('New Email Address') ?></div>
	<p>
		<i class="icon-warning-sign text-error"></i> <?= __('Since this action is irreversible, an email will be sent with a link to verify your decision to purge your account from the system.') ?>

		<hr/>

		<?= \Form::open(['onsubmit' => 'fuel_set_csrf_token(this);']) ?>
		<?= \Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token()) ?>

		<div class="control-group">
			<label class="control-label" for="password"><?= __('Password') ?></label>
			<div class="controls">
				<?= \Form::password([
					'id' => 'password',
					'name' => 'password',
					'placeholder' => __('Password'),
					'required' => true
				]) ?>
			</div>
		</div>

		<div class="control-group">
			<div class="controls">
				<?= \Form::submit(['class' => 'btn btn-primary', 'name' => 'submit', 'value' => __('Request Account Deletion')]) ?>
			</div>
		</div>

		<?= \Form::close() ?>
	</p>
</div>