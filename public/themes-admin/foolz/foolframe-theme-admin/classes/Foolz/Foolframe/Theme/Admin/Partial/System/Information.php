<?php

namespace Foolz\Foolframe\Theme\Admin\Partial\System;

class Information extends \Foolz\Theme\View
{
	public function toString()
	{?>
<?php foreach ($this->getParamManager()->getParam('info') as $key => $item) : ?>
<div class="admin-container">
	<div class="admin-container-header" id="<?= $key ?>"><?= $item['title'] ?></div>
	<table class="table table-hover table-condensed">
		<thead>
			<tr>
				<th class="span6"></th>
				<th class="span6"><?= _i('Value') ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($item['data'] as $k => $i) : ?>
			<tr>
				<td>
					<?php if (isset($i['description'])) : ?>
					<span data-placement="bottom" title="<?= htmlspecialchars($i['description']) ?>">
						<?= $i['title'] ?>
					</span>
					<?php else : ?>
					<?= $i['title'] ?>
					<?php endif; ?>

					<span class="pull-right">
						<?php if (isset($i['alert']) && $i['alert']['condition'] === false) : ?>
							<i class="icon-ok text-success"></i>
						<?php elseif (isset($i['alert']) && $i['alert']['condition'] === true) : ?>
							<a href="#<?= $key ?>" rel="popover" data-placement="right" data-trigger="hover" data-title="<?= htmlspecialchars(_i($i['alert']['title'])) ?>" data-content="<?= htmlspecialchars(_i($i['alert']['string'])) ?>">
								<?php if ($i['alert']['type'] == 'info') : ?>
									<i class="icon-exclamation-sign text-info"></i>
								<?php elseif ($i['alert']['type'] == 'warning') : ?>
									<i class="icon-warning-sign text-warning"></i>
								<?php elseif ($i['alert']['type'] == 'important') : ?>
									<i class="icon-remove text-error"></i>
								<?php endif; ?>
							</a>
						<?php endif; ?>
					</span>
				</td>
				<td><?= $i['value'] ?></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
<?php endforeach; ?>
<?php
	}
}