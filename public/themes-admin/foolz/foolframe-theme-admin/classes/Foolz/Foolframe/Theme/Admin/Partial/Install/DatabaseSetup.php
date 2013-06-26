<?php

namespace Foolz\Foolframe\Theme\Admin\Partial\Install;

class DatabaseSetup extends \Foolz\Theme\View
{
	public function toString()
	{
		?>
		<p class="description">
			<?= _i('Please enter the connection details to the MySQL database.') ?>
		</p>

		<div style="padding-top:20px;">
			<?= \Form::open(array('class' => 'form-horizontal')) ?>
				<fieldset>
					<div class="control-group">
						<label class="control-label" for="hostname"><?= _i('Database Type') ?></label>
						<div class="controls">
							<?= \Form::select('type', \Input::post('type', 'pdo_mysql'), array('pdo_mysql' => 'MySQL', 'pdo_pgsql' => 'PostgreSQL')); ?>
							<p class="help-block small-text"><?= _i('The database software you are using.') ?></p>
						</div>
					</div>

					<div class="control-group">
						<label class="control-label" for="hostname"><?= _i('Database Hostname') ?></label>
						<div class="controls">
							<?= \Form::input(array('id' => 'hostname', 'name' => 'hostname', 'value' => \Input::post('hostname', 'localhost'))) ?>
							<p class="help-block small-text"><?= _i('Unless you are using a remote database server for this FoolFrame installation, leave it as `localhost`.') ?></p>
						</div>
					</div>

					<div class="control-group">
						<label class="control-label" for="database"><?= _i('Database Name') ?></label>
						<div class="controls">
							<?= \Form::input(array('id' => 'database', 'name' => 'database', 'value' => \Input::post('database'))) ?>
							<p class="help-block small-text"><?= _i('This is the name of the database which will store your FoolFrame installation.') ?></p>
						</div>
					</div>

					<div class="control-group">
						<label class="control-label" for="username"><?= _i('Username') ?></label>
						<div class="controls">
							<?= \Form::input(array('id' => 'username', 'name' => 'username', 'value' => \Input::post('username'))) ?>
							<p class="help-block small-text"><?= _i('This is the username of the account used to access the database server specified above.') ?></p>
						</div>
					</div>

					<div class="control-group">
						<label class="control-label" for="password"><?= _i('Password') ?></label>
						<div class="controls">
							<?= \Form::password(array('id' => 'password', 'name' => 'password', 'value' => \Input::post('password'))) ?>
							<p class="help-block small-text"><?= _i('Enter the password for the account specified above.') ?></p>
						</div>
					</div>

					<div class="control-group">
						<label class="control-label" for="prefix"><?= _i('Table Prefix') ?></label>
						<div class="controls">
							<?= \Form::input(array('id' => 'prefix', 'name' => 'prefix', 'value' => \Input::post('prefix', 'ff_'))) ?>
							<p class="help-block small-text"><?= _i('If you wish to run multiple FoolFrame installations in a single database, change this.') ?></p>
						</div>
					</div>

					<hr>

					<?= \Form::submit(array('name' => 'submit', 'value' => _i('Next'), 'class' => 'btn btn-success pull-right')) ?>
				</fieldset>
			<?= \Form::close() ?>
		</div>
		<?php
	}
}