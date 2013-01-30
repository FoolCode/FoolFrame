<div class="admin-container">
	<div class="admin-container-header"><?= __('Users') ?></div>
	<table class="table table-hover table-condensed">
		<thead>
			<tr>
				<th><?= __('ID') ?></th>
				<th><?= __('Username') ?></th>
				<th><?= __('E-mail') ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($users as $user) : ?>
			<tr>
				<td class="span1"><?= $user->id ?></td>
				<td class="span5">
					<a href="<?= \Uri::create('admin/users/user/'.$user->id) ?>"><?= $user->username ?></a>
				</td>
				<td class="span5">
					<a href="mailto:<?= htmlspecialchars($user->email) ?>"><?= $user->email ?></a>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>