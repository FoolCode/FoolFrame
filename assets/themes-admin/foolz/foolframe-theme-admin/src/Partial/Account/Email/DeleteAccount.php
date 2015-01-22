<?php

namespace Foolz\Foolframe\Theme\Admin\Partial\Account\Email;

class DeleteAccount extends \Foolz\Foolframe\View\View
{
    public function toString()
    { ?>
<h2><?= $this->getParamManager()->getParam('title') ?></h2>

<h4>Hello <?= $this->getParamManager()->getParam('username') ?>!</h4>

Looks like you have requested for your account on <?= $this->getParamManager()->getParam('site') ?> to be deleted.
<br/><br/>
If this mail was sent to you by mistake, you can just ignore it. Sorry for bothering you! （´・ω・`）
<br/><br/>
Otherwise, you can delete your account by following <strong><a href="<?= $this->getParamManager()->getParam('link') ?>">this link</a></strong>.
<br/><br/>
If the link does not work, copy and paste the following address into your browser's address bar: <?= $this->getParamManager()->getParam('link') ?>
<br/><br/>
<hr/>
The <?= $this->getParamManager()->getParam('site') ?> team.
<?php
    }
}
