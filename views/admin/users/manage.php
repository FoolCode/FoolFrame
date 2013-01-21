<table class="table table-bordered table-striped table-condensed">
	<thead>
		<tr>
			<th><?= __('ID') ?></th>
			<th><?= __('Username') ?></th>
			<th><?= __('Email') ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($users as $user) : ?>
			<tr>
				<td>
					<a href="<?= Uri::create('admin/users/user/' . $user->id) ?>">
						<?php echo $user->id ?>
					</a>
				</td>
				<td>
					<a href="<?= Uri::create('admin/users/user/' . $user->id) ?>">
						<?= e($user->username) ?>
					</a>
				</td>
				<td>
					<a href="mailto:<?= htmlspecialchars($user->email); ?>"><?= e($user->email) ?></a>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>