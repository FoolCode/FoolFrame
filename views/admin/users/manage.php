<table class="table table-bordered table-striped table-condensed">
	<thead>
		<tr>
			<th><?= __('ID') ?></th>
			<th><?= __('Username') ?></th>
			<th><?= __('Display name') ?></th>
			<th><?= __('Twitter') ?></th>
			<th><?= __('Email') ?></th>
			<th><?= __('Last seen') ?></th>
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
				<td><?= e($user->display_name) ?></td>
				<td>
					<?php if (e($user->twitter)) : ?>
						<a target="_blank" href="http://twitter.com/<?= e($user->twitter) ?>">
							<?= e($user->twitter) ?>
						</a>
					<?php endif; ?>
				</td>
				<td>
					<a href="mailto:<?= htmlspecialchars($user->email); ?>"><?= e($user->email) ?></a>
				</td>
				<td><?php echo date('D M j G:i:s T Y', $user->last_login) ?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>