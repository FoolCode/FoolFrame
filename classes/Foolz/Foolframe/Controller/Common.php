<?php

namespace Foolz\Foolframe\Controller;

class Common extends \Controller
{

	public function before()
	{
		if ( ! \Foolz\Config\Config::get('foolz/foolframe', 'config', 'install.installed'))
		{
			throw new HttpNotFoundException;
		}

		parent::before();
	}


}