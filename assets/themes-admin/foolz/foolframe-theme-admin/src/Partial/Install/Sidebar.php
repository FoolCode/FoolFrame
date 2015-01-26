<?php

namespace Foolz\FoolFrame\Theme\Admin\Partial\Install;

class Sidebar extends \Foolz\FoolFrame\View\View
{
    public function toString()
    {
        $sidebar = $this->getParamManager()->getParam('sidebar');
        $current = $this->getParamManager()->getParam('current');

        ?>
        <div class="well sidebar-nav">
            <ul class="nav nav-list">
                <?php $counter = 0; ?>
                <?php foreach ($sidebar as $key => $item) : ?>
                    <?php if ($current !== false) : $counter ++; endif; ?>

                    <?php if ($key == $current) : ?>
                        <?php $current = false; ?>
                        <li class="active"><a name="<?= $item ?>"><?= $item ?></a></li>
                    <?php else : ?>
                        <li><a name="<?= $item ?>"><?= $item ?></a></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>

        <?php $percentage = floor(($counter - 1) / (count($sidebar) - 1) * 100); ?>
        <div class="progress progress-striped <?= ($percentage != 100) ? 'active' : 'progress-success' ?>"
             style="margin-top: 20px;">
            <div class="bar" style="width: <?= $percentage ?>%"></div>
        </div>
        <?php
    }
}
