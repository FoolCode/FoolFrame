<?php

namespace Foolz\Foolframe\Controller;

class Common
{
	public function before()
	{
		if ( ! \Foolz\Config\Config::get('foolz/foolframe', 'config', 'install.installed'))
		{
			throw new HttpNotFoundException;
		}
	}
}