<?php

namespace Foolz\Foolframe\Theme\Admin\Partial\Install;

class Complete extends \Foolz\Foolframe\View\View
{
    public function toString()
    {
        ?>
        <p class="text-success">
            <?= _i('Congratulations! The installation is complete!') ?>
        </p>

        <hr>

        <a href="<?= $this->getUri()->create('admin') ?>" class="btn btn-info pull-right"><?= _i('Log In') ?></a>
        <?php
    }
}
