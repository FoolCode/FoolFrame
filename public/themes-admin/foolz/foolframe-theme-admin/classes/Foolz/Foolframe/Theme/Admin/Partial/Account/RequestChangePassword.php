<?php

namespace Foolz\Foolframe\Theme\Admin\Partial\Account;

class RequestChangePassword extends \Foolz\Foolframe\View\View
{
    public function toString()
    { ?>
<div class="admin-container">
    <div class="admin-container-header"><?= _i('Change Password') ?></div>
    <p>
        <?= _i('To change your password click the button below.<br>An email will be sent to your registered email account with further instructions.') ?>

        <hr/>

        <?= \Form::open(['onsubmit' => 'fuel_set_csrf_token(this);']) ?>
        <?= \Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token()) ?>
        <?= \Form::submit(['class' => 'btn btn-primary', 'name' => 'submit', 'value' => _i('Request Password Change')]) ?>
        <?= \Form::close() ?>
    </p>
</div>
<?php
    }
}
