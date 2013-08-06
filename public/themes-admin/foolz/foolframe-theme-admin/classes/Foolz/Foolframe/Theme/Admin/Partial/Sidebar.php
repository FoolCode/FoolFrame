<?php

namespace Foolz\Foolframe\Theme\Admin\Partial;

class Sidebar extends \Foolz\Foolframe\View\View
{

    public function toString()
    {
        $sidebar = $this->getParamManager()->getParam('sidebar');
    ?>

<?php if (count($sidebar) > 0) : ?>
<div class="well sidebar-nav">
    <ul class="nav nav-list">
    <?php foreach ($sidebar as $key => $item) : ?>
        <li class="nav-header">	<?= $item['name'] ?></li>
        <?php foreach ($item['content'] as $k => $i) : ?>
            <?php if ($i['active']) : ?>
                <li class="active">
                    <a href="<?= $i['href'] ?>">
                        <i class="<?= $i['icon'] ?> icon-white"></i> <?= $i['name'] ?>
                        <?php if (isset($i['notification'])) : ?>
                            <span class="badge badge-inverse pull-right">
                                <?= $i['notification'] ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>
            <?php else : ?>
                <li>
                    <a href="<?= $i['href'] ?>">
                        <i class="<?= $i['icon'] ?>"></i> <?= $i['name'] ?>
                        <?php if (isset($i['notification'])) : ?>
                            <span class="badge badge-info pull-right">
                                <?= $i['notification'] ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>
<?php
    }
}
