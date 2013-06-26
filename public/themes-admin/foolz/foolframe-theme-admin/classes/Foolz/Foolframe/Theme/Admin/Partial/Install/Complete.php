<?php

namespace Foolz\Foolframe\Theme\Admin\Partial\Install;

class Complete extends \Foolz\Theme\View
{
	public function toString()
	{
		?>
		<p class="text-success">
			<?= __('Congratulations! The installation is complete!') ?>
		</p>

		<hr/>

		<a href="<?= \Uri::create('admin') ?>" class="btn btn-info pull-right"><?= __('Log In') ?></a>
		<?php
	}
}