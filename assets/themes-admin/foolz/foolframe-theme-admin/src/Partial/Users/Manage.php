<?php

namespace Foolz\FoolFrame\Theme\Admin\Partial\Users;

class Manage extends \Foolz\FoolFrame\View\View
{
    public function toString()
    {?>
<div class="admin-container">
    <div class="admin-container-header"><?= _i('Users') ?></div>
    <table class="table table-hover table-condensed">
        <thead>
            <tr>
                <th class="span1"><?= _i('ID') ?></th>
                <th class="span5"><?= _i('Username') ?></th>
                <th class="span5"><?= _i('Email') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($this->getParamManager()->getParam('users') as $user) : ?>
            <tr>
                <td><?= $user->id ?></td>
                <td>
                    <a href="<?= $this->getUri()->create('admin/users/user/'.$user->id) ?>"><?= $user->username ?></a>
                </td>
                <td>
                    <a href="mailto:<?= htmlspecialchars($user->email) ?>"><?= $user->email ?></a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php
    }
}
