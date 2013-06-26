<?php

namespace Foolz\Foolframe\Theme\Admin\Partial\Install;

class Navbar extends \Foolz\Theme\View
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