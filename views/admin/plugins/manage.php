<?php foreach ($plugins as $module => $module_plugins) : ?>
<div class="admin-container">
	<div class="admin-container-header">
		<?= \Str::tr(__(':module Plugins'), ['module' => \Foolz\Config\Config::get($module, 'package', 'main.name')]) ?>
	</div>
	<table class="table table-hover table-condensed">
		<thead>
			<tr>
				<th><?= __('Plugin Name') ?></th>
				<th><?= __('Description') ?></th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($module_plugins as $plugin) : ?>
			<tr>
				<td class="span2">
					<?php echo $plugin->getJsonConfig('extra.name', $plugin->getJsonConfig('name')) ?>
				</td>
				<td class="span8 muted">
					<?php echo $plugin->getJsonConfig('description') ?>
				</td>
				<td class="span2">
					<div class="btn-group pull-right">
					<?= \Form::open(
						'admin/plugins/action',
						[
							'action' => (isset($plugin->enabled) ? 'disable' : 'enable'),
							'module' => $module,
							'name' => $plugin->getJsonConfig('name')
						]
					) ?>
					<?= \Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token()) ?>
					<?= \Form::submit([
						'class' => (isset($plugin->enabled) ? 'btn btn-small btn-warning' : 'btn btn-small btn-success'),
						'name' => 'submit',
						'value' => (isset($plugin->enabled) ? __('Disable') : __('Enable'))
					]) ?>
					<?= \Form::close(); ?>
					</div>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
<?php endforeach; ?>