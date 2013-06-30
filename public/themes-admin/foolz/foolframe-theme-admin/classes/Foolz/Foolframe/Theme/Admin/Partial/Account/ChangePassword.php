<?php

namespace Foolz\Foolframe\Theme\Admin\Partial\Account;

class ChangePassword extends \Foolz\Theme\View
{
public function toString()
{ ?><div class="admin-container">
    <div class="admin-container-header"><?= _i('New Password') ?></div>
    <p>
        <?= \Form::open(['onsubmit' => 'fuel_set_csrf_token(this);']) ?>
        <?= \Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token()) ?>

        <div class="control-group">
            <label class="control-label" for="new-password"><?= _i('Password') ?></label>
            <div class="controls">
                <?= \Form::password([
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
                <?= \Form::password([
                    'id' => 'new-password-confirm',
                    'name' => 'confirm_password',
                    'placeholder' => _i('Password'),
                    'required' => true
                ]) ?>
            </div>
        </div>

        <div class="control-group">
            <div class="controls">
                <?= \Form::submit(['class' => 'btn btn-primary', 'name' => 'submit', 'value' => _i('Submit')]) ?>
            </div>
        </div>

        <?= \Form::close() ?>
    </p>
</div>
<?php
    }
}
