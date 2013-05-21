<?php

namespace Foolz\Foolframe\Theme\Admin\Partial;

class Content extends \Foolz\Theme\View
{
	public function toString()
	{
		echo $this->getParamManager()->getParam('content');
	}
}