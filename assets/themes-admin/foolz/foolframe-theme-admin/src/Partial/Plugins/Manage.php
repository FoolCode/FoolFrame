<?php

namespace Foolz\FoolFrame\Theme\Admin\Partial\Plugins;

use Foolz\FoolFrame\Model\Legacy\Config;

class Manage extends \Foolz\FoolFrame\View\View
{
    public function toString()
    {
        $form = $this->getForm();
        ?>
<div class="admin-container">
    <div class="admin-container-header">
        <?= _i('All Plugins') ?><?php /*<?= \Str::tr(__(':module Plugins'), ['module' => Config::get($module, 'package', 'main.name')]) ?> */ ?>
    </div>
    <table class="table table-hover table-condensed">
        <thead>
            <tr>
                <th class="span2"><?= _i('Plugin Name') ?></th>
                <th class="span8"><?= _i('Description') ?></th>
                <th class="span2"></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($this->getParamManager()->getParam('plugins') as $plugin) : ?>
            <tr>
                <td>
                    <?= $plugin->getJsonConfig('extra.name', $plugin->getJsonConfig('name')) ?>
                </td>
                <td class="muted">
                    <?= $plugin->getJsonConfig('description') ?>
                </td>
                <td>
                    <div class="btn-group pull-right">
                    <?= $form->open(
                        'admin/plugins/action',
                        [
                            'action' => (isset($plugin->enabled) ? 'disable' : 'enable'),
                            'name' => $plugin->getJsonConfig('name')
                        ]
                    ) ?>
                    <?= $form->hidden('csrf_token', $this->getSecurity()->getCsrfToken()) ?>
                    <?= $form->submit([
                        'class' => (isset($plugin->enabled) ? 'btn btn-small btn-warning' : 'btn btn-small btn-success'),
                        'name' => 'submit',
                        'value' => (isset($plugin->enabled) ? _i('Disable') : _i('Enable'))
                    ]) ?>
                    <?= $form->close(); ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php
    }
}
