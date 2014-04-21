<?php

namespace Foolz\Foolframe\Theme\Admin\Partial\Account;

class RequestDelete extends \Foolz\Foolframe\View\View
{
    public function toString()
    {
        $form = $this->getForm();
        ?>
<div class="admin-container">
    <div class="admin-container-header"><?= _i('New Email Address') ?></div>
    <p>
        <i class="icon-warning-sign text-error"></i> <?= _i('This action is irreversible, Insert your account password below and click the button.<br>An email will be sent to your registered email account with further instructions to verify your decision to purge your account from the system.') ?>

        <hr/>

        <?= $form->open(['onsubmit' => 'fuel_set_csrf_token(this);']) ?>
        <?= $form->hidden('csrf_token', $this->getSecurity()->getCsrfToken()) ?>

        <div class="control-group">
            <label class="control-label" for="password"><?= _i('Password') ?></label>
            <div class="controls">
                <?= $form->password([
                    'id' => 'password',
                    'name' => 'password',
                    'placeholder' => _i('Password'),
                    'required' => true
                ]) ?>
            </div>
        </div>

        <div class="control-group">
            <div class="controls">
                <?= $form->submit(['class' => 'btn btn-primary', 'name' => 'submit', 'value' => _i('Request Account Deletion')]) ?>
            </div>
        </div>

        <?= $form->close() ?>
    </p>
</div>
    <?php
    }
}
