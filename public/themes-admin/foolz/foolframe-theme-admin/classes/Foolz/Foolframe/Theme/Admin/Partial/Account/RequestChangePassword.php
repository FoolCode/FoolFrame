<?php

namespace Foolz\Foolframe\Theme\Admin\Partial\Account;

class RequestChangePassword extends \Foolz\Foolframe\View\View
{
    public function toString()
    {
        $form = $this->getForm();
        ?>
<div class="admin-container">
    <div class="admin-container-header"><?= _i('Change Password') ?></div>
    <p>
        <?= _i('To change your password click the button below.<br>An email will be sent to your registered email account with further instructions.') ?>

        <hr/>

        <?= $form->open(['onsubmit' => 'fuel_set_csrf_token(this);']) ?>
        <?= $form->hidden('csrf_token', $this->getSecurity()->getCsrfToken()) ?>
        <?= $form->submit(['class' => 'btn btn-primary', 'name' => 'submit', 'value' => _i('Request Password Change')]) ?>
        <?= $form->close() ?>
    </p>
</div>
<?php
    }
}
