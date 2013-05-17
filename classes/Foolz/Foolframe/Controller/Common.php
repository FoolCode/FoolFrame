<?php

namespace Foolz\Foolframe\Controller;

use Symfony\Component\HttpFoundation\Request;

class Common
{
	public function before(Request $request)
	{
		if ( ! \Foolz\Config\Config::get('foolz/foolframe', 'config', 'install.installed'))
		{
			throw new HttpNotFoundException;
		}
	}
}