<?php

namespace Foolz\Foolframe\Theme\Admin\Partial\Install;

class Welcome extends \Foolz\Theme\View
{
	public function toString()
	{
		?>
		<p class="description"><?= __('FoolFrame is a custom, light-weight framework that utilizes multiple open source components. It provides a very basic foundation for all Foolz Software and serves as an abstraction layer for our modules.') ?></p>
		<p class="info"><?= __('The installation procedure is divided into six simple steps and should take approximately 5 minutes to complete.') ?></p>

		<hr>

		<a href="<?= \Uri::create('install/system_check') ?>" class="btn btn-success pull-right"><?= __('Next') ?></a>
		<?php
	}
}