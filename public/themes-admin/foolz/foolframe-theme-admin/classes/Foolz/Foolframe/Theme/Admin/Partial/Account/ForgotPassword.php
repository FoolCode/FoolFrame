<?php

namespace Foolz\Foolframe\Theme\Admin\Partial\Account;

class ForgotPassword extends \Foolz\Foolframe\View\View
{
    public function toString()
    { ?>
<?= \Form::open(['class' => 'form-account', 'onsubmit' => 'fuel_set_csrf_token(this);']) ?>
    <?= \Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token()); ?>
    <h2 class="form-account-heading"><?= _i('Forgot Password') ?></h2>

    <?= \Form::input([
        'class' => 'input-block-level',
        'name' => 'email',
        'type' => 'email',
        'value' => $this->getPost('email'),
        'placeholder' => _i('Email Address'),
        'required' => true
    ]) ?>

    <?= \Form::submit(['class' => 'btn btn-primary', 'name' => 'submit', 'value' => _i('Submit')]) ?>

    <input type="button" onClick="window.location.href='<?= $this->getUri()->create('/admin/account/login/') ?>'" class="btn" value="<?= htmlspecialchars(_i('Back')) ?>" />
<?= \Form::close() ?>
<?php
    }
}
