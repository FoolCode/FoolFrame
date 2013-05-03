<?php

namespace Foolz\Foolframe\Model;

use Symfony\Component\HttpFoundation\Request;

class Uri
{

	/**
	 * @var null|Request
	 */
	public static $request = null;

	public static function setRequest(Request $request)
	{
		static::$request = $request;
	}

	public static function base()
	{
		return static::$request->getUriForPath('/');
	}

	public static function create($uri)
	{
		return static::$request->getUriForPath('/'.(is_array($uri) ? implode('/', $uri) : $uri)).'/';
	}
}