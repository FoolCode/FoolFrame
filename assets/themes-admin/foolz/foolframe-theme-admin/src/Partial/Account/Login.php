<?php

namespace Foolz\FoolFrame\Theme\Admin\Partial\Account;

class Login extends \Foolz\FoolFrame\View\View
{
    public function toString()
    {
        $form = $this->getForm();
        ?>
        <?= $form->open(['class' => 'form-account', 'onsubmit' => 'fuel_set_csrf_token(this);']) ?>
        <?= $form->hidden('csrf_token', $this->getSecurity()->getCsrfToken()); ?>
        <h2 class="form-account-heading"><?= _i('Login') ?></h2>

        <hr>

        <?= $form->input([
        'class' => 'input-block-level',
        'name' => 'username',
        'value' => $this->getPost('username'),
        'placeholder' => _i('Username')
    ]) ?>

        <?= $form->password([
        'class' => 'input-block-level',
        'name' => 'password',
        'placeholder' => _i('Password')
    ]) ?>

        <label class="checkbox">
            <?= $form->checkbox([
                'name' => 'remember',
                'value' => true,
                'checked' => $this->getPost('remember')
            ]) ?>
            <?= $form->label(_i('Remember Me'), 'remember') ?>
        </label>

        <?= $form->submit(['class' => 'btn btn-primary', 'name' => 'submit', 'value' => _i('Login')]) ?>

        <input type="button" class="btn" onClick="window.location.href='<?= $this->getUri()->create('/admin/account/forgot_password/') ?>'" value="<?= htmlspecialchars(_i('Forgot Password')) ?>" />

        <?php if (!$this->getPreferences()->get('foolframe.auth.disable_registration')) : ?>
        <input type="button" class="btn" onClick="window.location.href='<?= $this->getUri()->create('/admin/account/register/') ?>'" value="<?= htmlspecialchars(_i('Register')) ?>" />
    <?php endif; ?>

        <hr>

        <a href="<?= $this->getUri()->base() ?>"><?= _i('Back to Index') ?></a>
        <?= $form->close() ?>
    <?php
    }
}
