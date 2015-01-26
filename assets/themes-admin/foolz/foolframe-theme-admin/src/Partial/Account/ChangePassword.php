<?php

namespace Foolz\FoolFrame\Theme\Admin\Partial\Account;

class ChangePassword extends \Foolz\FoolFrame\View\View
{
    public function toString()
    {
        $form = $this->getForm();

        ?><div class="admin-container">
        <div class="admin-container-header"><?= _i('New Password') ?></div>
        <p>
            <?= $form->open(['onsubmit' => 'fuel_set_csrf_token(this);']) ?>
            <?= $form->hidden('csrf_token', $this->getSecurity()->getCsrfToken()) ?>

            <div class="control-group">
                <label class="control-label" for="new-password"><?= _i('Password') ?></label>
                <div class="controls">
                    <?= $form->password([
                        'id' => 'new-password',
                        'name' => 'password',
                        'placeholder' => _i('Password'),
                        'required' => true
                    ]) ?>
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="new-password-confirm"><?= _i('Confirm Password') ?></label>
                <div class="controls">
                    <?= $form->password([
                        'id' => 'new-password-confirm',
                        'name' => 'confirm_password',
                        'placeholder' => _i('Password'),
                        'required' => true
                    ]) ?>
                </div>
            </div>

            <div class="control-group">
                <div class="controls">
                    <?= $form->submit(['class' => 'btn btn-primary', 'name' => 'submit', 'value' => _i('Submit')]) ?>
                </div>
            </div>

            <?= $form->close() ?>
        </p>
    </div>
    <?php
    }
}
