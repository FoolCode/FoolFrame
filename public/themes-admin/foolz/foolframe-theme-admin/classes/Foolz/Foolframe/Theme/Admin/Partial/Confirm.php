<?php

namespace Foolz\Foolframe\Theme\Admin\Partial;

class Confirm extends \Foolz\Theme\View
{
	public function toString()
	{ ?>
<div class="alert alert-block alert-<?= $this->getParamManager()->getParam('alert_level') ?> fade in">
	<p><?= $this->getParamManager()->getParam('message') ?></p>
	<p><?php echo \Form::open(array('onsubmit' => 'fuel_set_csrf_token(this);'));
		echo \Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());
		echo \Form::submit(array(
			'name' => 'confirm',
			'value' => _i('Confirm'),
			'class' => 'btn btn-danger',
			'style' => 'margin-right:6px;'));
		echo '<input type="button" onClick="history.back()" class="btn" value="'. _i('Go back') . '" />';
		echo \Form::close();
	?></p>
</div>
<?php
	}
}