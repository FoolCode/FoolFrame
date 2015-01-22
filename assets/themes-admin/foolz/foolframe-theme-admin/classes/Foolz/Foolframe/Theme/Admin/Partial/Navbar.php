<?php

namespace Foolz\Foolframe\Theme\Admin\Partial;

class Navbar extends \Foolz\Foolframe\View\View
{

public function toString()
{ ?>
<div class="navbar navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container">
            <a class="brand" href="<?= $this->getUri()->create('admin') ?>">
                <?= $this->getPreferences()->get('foolframe.gen.website_title') ?>
            </a>
            <ul class="nav pull-right">
                <li><a href="<?= $this->getUri()->base('@default') ?>"><?= _i('Boards') ?></a></li>
                <li class="divider-vertical"></li>
                <?php if ($this->getAuth()->hasAccess('maccess.user')) : ?>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <?= $this->getAuth()->getUser()->getUsername(); ?>
                            <b class="caret"></b>
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="<?= $this->getUri()->create('admin/account/change_email') ?>">
                                    <?= _i('Profile') ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?= $this->getUri()->create('/admin/account/logout').'?csrf_token='.$this->getSecurity()->getCsrfToken() ?>">
                                    <?= _i('Logout') ?>
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php else : ?>
                    <li>
                        <a href="<?= $this->getUri()->create('admin/account/login') ?>">
                            <?= _i('Login') ?>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>
<?php
    }
}
