<?php

namespace Foolz\Foolframe\Theme\Admin\Partial\Plugins;

use Foolz\Config\Config;

class Manage extends \Foolz\Theme\View
{
	public function toString()
	{ ?>
<?php foreach ($this->getParamManager()->getParam('plugins') as $module => $module_plugins) : ?>
<div class="admin-container">
	<div class="admin-container-header">
		<?php /*<?= \Str::tr(__(':module Plugins'), ['module' => Config::get($module, 'package', 'main.name')]) ?> */ ?>
	</div>
	<table class="table table-hover table-condensed">
		<thead>
			<tr>
				<th class="span2"><?= __('Plugin Name') ?></th>
				<th class="span8"><?= __('Description') ?></th>
				<th class="span2"></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($module_plugins as $plugin) : ?>
			<tr>
				<td>
					<?= $plugin->getJsonConfig('extra.name', $plugin->getJsonConfig('name')) ?>
				</td>
				<td class="muted">
					<?= $plugin->getJsonConfig('description') ?>
				</td>
				<td>
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
<?php
	}
}