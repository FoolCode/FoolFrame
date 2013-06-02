<?php

namespace Foolz\Foolframe\Theme\Admin\Partial\Users;

class Manage extends \Foolz\Theme\View
{
	public function toString()
	{?>
<div class="admin-container">
	<div class="admin-container-header"><?= __('Users') ?></div>
	<table class="table table-hover table-condensed">
		<thead>
			<tr>
				<th class="span1"><?= __('ID') ?></th>
				<th class="span5"><?= __('Username') ?></th>
				<th class="span5"><?= __('Email') ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($this->getParamManager()->getParam('users') as $user) : ?>
			<tr>
				<td><?= $user->id ?></td>
				<td>
					<a href="<?= \Uri::create('admin/users/user/'.$user->id) ?>"><?= $user->username ?></a>
				</td>
				<td>
					<a href="mailto:<?= htmlspecialchars($user->email) ?>"><?= $user->email ?></a>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
<?php
	}
}