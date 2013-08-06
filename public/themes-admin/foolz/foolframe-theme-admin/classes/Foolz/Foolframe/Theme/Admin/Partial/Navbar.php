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
                <?php if (\Auth::has_access('maccess.user')) : ?>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <?= \Auth::get_screen_name(); ?>
                            <b class="caret"></b>
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="<?= $this->getUri()->create('admin/account/change_email') ?>">
                                    <?= _i('Profile') ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?= $this->getUri()->create('/admin/account/logout').'?token='.\Security::fetch_token() ?>">
                                    <?= _i('Logout') ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?= $this->getUri()->create('/admin/account/logout_all').'?token='.\Security::fetch_token() ?>">
                                    <?= _i('Logout on All Devices') ?>
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
