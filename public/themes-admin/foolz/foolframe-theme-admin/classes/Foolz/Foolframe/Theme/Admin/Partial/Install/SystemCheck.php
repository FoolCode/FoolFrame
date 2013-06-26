<?php

namespace Foolz\Foolframe\Theme\Admin\Partial\Install;

class SystemCheck extends \Foolz\Theme\View
{
	public function toString()
	{
		$system = $this->getParamManager()->getParam('system');

		?>
		<p class="description">
			<?= __('FoolFrame is checking your server environment to ensure that your server meets the minimum requirements needed to run our software properly.') ?>
		</p>

		<?php $error = false ?>
		<?php foreach ($system as $key => $item) : ?>
		<div class="system-check-container">
			<div class="system-check-container-header" id="<?= $key ?>"><?= $item['title'] ?></div>
			<table class="table table-hover table-condensed">
				<thead>
				<tr>
					<th class="span4"></th>
					<th class="span4"><?= __('Value') ?></th>
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
							<a href="#<?= $key ?>" rel="popover" data-placement="right" data-trigger="hover"
							   data-title="<?= htmlspecialchars(__($i['alert']['title'])) ?>"
							   data-content="<?= htmlspecialchars(__($i['alert']['string'])) ?>">
								<?php if ($i['alert']['type'] == 'info') : ?>
									<i class="icon-exclamation-sign text-info"></i>
								<?php elseif ($i['alert']['type'] == 'warning') : ?>
									<i class="icon-warning-sign text-warning"></i>
								<?php
								elseif ($i['alert']['type'] == 'important') : ?>
									<?php $error = true; ?>
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

		<?php if ($error === true) : ?>
		<p class="text-warning"
		   style="text-align: center;"><?= e(__('Sorry, your server environment failed to pass all of the minimum requirements needed to run the software properly. Please review the information above and ensure that your server environment is properly configured.')) ?></p>
	<?php else : ?>
		<p class="text-success"
		   style="text-align: center;"><?= e(__('Congratulations! Your server environment meets all of the minimum requirements to run the software properly.')) ?></p>

		<hr/>

		<a href="<?= \Uri::create('install/database_setup') ?>" class="btn btn-success pull-right"><?= __('Next') ?></a>
	<?php endif;
	}
}