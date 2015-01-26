<?php

namespace Foolz\FoolFrame\Theme\Admin\Partial\Install;

class Navbar extends \Foolz\FoolFrame\View\View
{

public function toString()
{ ?>
<div class="navbar navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container">
            <a class="brand">
                <?= $this->getBuilder()->getProps()->getTitle() ?>
            </a>
        </div>
    </div>
</div>
<?php
    }
}
