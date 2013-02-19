<div class="admin-container">
	<div class="admin-container-header"><?= __('Change Email Address') ?></div>
	<p>
		<?= \Form::open(['onsubmit' => 'fuel_set_csrf_token(this);']) ?>
		<?= \Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token()) ?>

		<div class="control-group">
			<label class="control-label" for="new-email"><?= __('New Email Address') ?></label>
			<div class="controls">
				<?= \Form::input([
					'id' => 'new-email',
					'name' => 'email',
					'type' => 'email',
					'value' => \Input::post('email'),
					'placeholder' => 'test@example.com',
					'required' => true
				]) ?>
			</div>
		</div>

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
				<?= \Form::submit(['class' => 'btn btn-primary', 'name' => 'submit', 'value' => __('Submit')]) ?>
			</div>
		</div>

		<?= \Form::close() ?>
	</p>
</div>