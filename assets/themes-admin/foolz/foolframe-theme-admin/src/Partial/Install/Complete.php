<?php

namespace Foolz\FoolFrame\Theme\Admin\Partial\Install;

class Complete extends \Foolz\FoolFrame\View\View
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
