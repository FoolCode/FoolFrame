<?php

namespace Foolz\Foolframe\Theme\Admin\Partial\Account;

class Login extends \Foolz\Theme\View
{
    public function toString()
    { ?>
        <?= \Form::open(['class' => 'form-account', 'onsubmit' => 'fuel_set_csrf_token(this);']) ?>
        <?= \Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token()); ?>
        <h2 class="form-account-heading"><?= _i('Login') ?></h2>

        <hr>

        <?= \Form::input([
        'class' => 'input-block-level',
        'name' => 'username',
        'value' => \Input::post('username'),
        'placeholder' => _i('Username')
    ]) ?>

        <?= \Form::password([
        'class' => 'input-block-level',
        'name' => 'password',
        'placeholder' => _i('Password')
    ]) ?>

        <label class="checkbox">
            <?= \Form::checkbox([
                'name' => 'remember',
                'value' => true,
                'checked' => \Input::post('remember')
            ]) ?>
            <?= \Form::label(_i('Remember Me'), 'remember') ?>
        </label>

        <?= \Form::submit(['class' => 'btn btn-primary', 'name' => 'submit', 'value' => _i('Login')]) ?>

        <input type="button" class="btn" onClick="window.location.href='<?= \Uri::create('/admin/account/forgot_password/') ?>'" value="<?= htmlspecialchars(_i('Forgot Password')) ?>" />

        <?php if (!\Preferences::get('foolframe.auth.disable_registration')) : ?>
        <input type="button" class="btn" onClick="window.location.href='<?= \Uri::create('/admin/account/register/') ?>'" value="<?= htmlspecialchars(_i('Register')) ?>" />
    <?php endif; ?>

        <hr>

        <a href="<?= \Uri::base() ?>"><?= _i('Back to Index') ?></a>
        <?= \Form::close() ?>
    <?php
    }
}
