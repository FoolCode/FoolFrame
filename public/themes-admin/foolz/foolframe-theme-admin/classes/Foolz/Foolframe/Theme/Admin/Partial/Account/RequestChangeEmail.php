<?php

namespace Foolz\Foolframe\Theme\Admin\Partial\Account;

class RequestChangeEmail extends \Foolz\Theme\View
{
    public function toString()
    { ?>
<div class="admin-container">
    <div class="admin-container-header"><?= _i('Change Email Address') ?></div>
    <p>
        <?= \Form::open(['onsubmit' => 'fuel_set_csrf_token(this);']) ?>
        <?= \Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token()) ?>

        <div class="control-group">
            <label class="control-label" for="new-email"><?= _i('New Email Address') ?></label>
            <div class="controls">
                <?= \Form::input([
                    'id' => 'new-email',
                    'name' => 'email',
                    'type' => 'email',
                    'value' => \Input::post('email'),
                    'placeholder' => 'test@example.com',
                    'required' => true
                ]) ?>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="password"><?= _i('Password') ?></label>
            <div class="controls">
                <?= \Form::password([
                    'id' => 'password',
                    'name' => 'password',
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
