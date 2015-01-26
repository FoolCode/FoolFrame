<?php

namespace Foolz\FoolFrame\Theme\Admin\Partial\Install;

class Modules extends \Foolz\FoolFrame\View\View
{
    public function toString()
    {
        $form = $this->getForm();
        $modules = $this->getParamManager()->getParam('modules');
        ?>
        <p class="description">
            <?= _i('Congratulations, you have completed the installation and setup of FoolFrame. Please choose the module(s) you wish to install below:') ?>
        </p>

        <?= $form->open() ?>

            <?php foreach ($modules as $module => $info) : ?>
                <label class="checkbox">
                    <?php if ($info['disabled']) : ?>
                        <input type="checkbox" name="<?= $module ?>" disabled="disabled" />
                    <?php else : ?>
                        <input type="checkbox" name="<?= $module ?>" />
                    <?php endif; ?>
                    <?= $info['title'] ?>
                </label>
                <p style="font-size: 0.8em; padding-left: 20px"><?= $info['description'] ?></p>
            <?php endforeach; ?>

            <hr>

            <?= $form->submit(array('name' => 'submit', 'value' => _i('Next'), 'class' => 'btn btn-success pull-right')) ?>
        <?= $form->close() ?>
        <?php
    }
}
