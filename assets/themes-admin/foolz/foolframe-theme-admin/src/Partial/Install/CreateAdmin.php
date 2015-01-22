<?php

namespace Foolz\Foolframe\Theme\Admin\Partial\Install;

class CreateAdmin extends \Foolz\Foolframe\View\View
{
    public function toString()
    {
        $form = $this->getForm();
        ?>
        <p class="description">
            <?= _i('Please enter the following details to create the administrative account. This account will be used to manage the entire installation. It is important that you do not lose this information.') ?>
        </p>

        <div style="padding-top:20px;">
            <?= $form->open(array('class' => 'form-horizontal')) ?>
            <fieldset>
                <div class="control-group">
                    <label class="control-label" for="username"><?= _i('Username') ?></label>

                    <div class="controls">
                        <?= $form->input(array('id' => 'username', 'name' => 'username', 'value' => $this->getPost('username'))) ?>
                        <p class="help-block small-text"><?= _i('This will the the username of the account with administrative privileges created to manage your FoolFrame installation.') ?></p>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="email"><?= _i('Email') ?></label>

                    <div class="controls">
                        <?= $form->input(array('id' => 'email', 'name' => 'email', 'type' => 'email', 'value' => $this->getPost('email'))) ?>
                        <p class="help-block small-text"><?= _i('Enter the email address for the user account specified above. This will be used for account recovery and authentication.') ?></p>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="password"><?= _i('Password') ?></label>

                    <div class="controls">
                        <?= $form->password(array('id' => 'password', 'name' => 'password')) ?>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="confirm_password"><?= _i('Confirm Password') ?></label>

                    <div class="controls">
                        <?= $form->password(array('id' => 'confirm_password', 'name' => 'confirm_password')) ?>
                    </div>
                </div>

                <hr>

                <?= $form->submit(array('name' => 'submit', 'value' => _i('Next'), 'class' => 'btn btn-success pull-right')) ?>
            </fieldset>
            <?= $form->close() ?>
        </div>
    <?php
    }
}
