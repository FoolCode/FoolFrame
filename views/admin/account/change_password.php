<div class="admin-container">
	<div class="admin-container-header"><?= __('New Password') ?></div>
	<p>
		<?= \Form::open(['onsubmit' => 'fuel_set_csrf_token(this);']) ?>
		<?= \Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token()) ?>

		<div class="control-group">
			<label class="control-label" for="new-password"><?= __('Password') ?></label>
			<div class="controls">
				<?= \Form::password([
					'id' => 'new-password',
					'name' => 'password',
					'placeholder' => __('Password'),
					'required' => true
				]) ?>
			</div>
		</div>

		<div class="control-group">
			<label class="control-label" for="new-password-confirm"><?= __('Confirm Password') ?></label>
			<div class="controls">
				<?= \Form::password([
					'id' => 'new-password-confirm',
					'name' => 'confirm_password',
					'placeholder' => __('Password'),
					'required' => true
				]) ?>
			</div>
		</div>

		<div class="control-group">
			<div class="controls">
				<?= \Form::submit(['class' => 'btn btn-primary', 'name' => 'submit', 'value' => __('Submit')]) ?>
			</div>
		</div>

		<?= \Form::close() ?>
	</p>
</div>