<?php

namespace Foolz\Foolframe\Model;

use \Foolz\Foolframe\Model\DoctrineConnection as DC;
use \Foolz\Cache\Cache;

class Preferences
{
	protected static $_preferences = array();

	protected static $_module_identifiers = array();

	protected static $loaded = false;


	public static function load_settings($reload = false)
	{
		\Profiler::mark('Preferences::load_settings Start');
		if ($reload === true)
		{
			\Cache::delete('ff.model.preferences.settings');
		}

		// we need to know the identifiers of the modules, like ff => foolfuuka, fu => foolfuuka, fs => foolslide
		static::$_module_identifiers = \Foolz\Config\Config::get('foolz/foolframe', 'config', 'modules.installed');

		try
		{
			static::$_preferences = Cache::item('ff.model.preferences.settings')->get();
		}
		catch (\OutOfBoundsException $e)
		{
			$preferences = DC::qb()
				->select('*')
				->from(DC::p('preferences'), 'p')
				->execute()
				->fetchAll();

			foreach($preferences as $pref)
			{
				// fix the PHP issue where . is changed to _ in the $_POST array
				static::$_preferences[$pref['name']] = $pref['value'];
			}

			Cache::item('ff.model.preferences.settings')->set(static::$_preferences, 3600);
		}

		\Profiler::mark_memory(static::$_preferences, 'Preferences static::$_preferences');
		\Profiler::mark('Preferences::load_settings End');

		static::$loaded = true;

		return static::$_preferences;
	}


	public static function get($setting, $fallback = null)
	{
		if ( ! static::$loaded)
		{
			static::load_settings();
		}

		if (isset(static::$_preferences[$setting]) && static::$_preferences[$setting] !== '')
		{
			return static::$_preferences[$setting];
		}

		if ($fallback !== null)
		{
			return $fallback;
		}

		$segments = explode('.', $setting);
		$identifier = array_shift($segments);
		$query = implode('.', $segments);

		return \Foolz\Config\Config::get(static::$_module_identifiers[$identifier], 'package', 'preferences.'.$query);
	}


	public static function set($setting, $value, $reload = true)
	{
		// if array, serialize value
		if (is_array($value))
		{
			$value = serialize($value);
		}

		$count = DC::qb()
			->select('COUNT(*) as count')
			->from(DC::p('preferences'), 'p')
			->where('p.name = :name')
			->setParameter(':name', $setting)
			->execute()
			->fetch();

		if ($count['count'])
		{
			DC::qb()
				->update(DC::p('preferences'))
				->set('value', ':value')
				->where('name', ':name')
				->setParameters([':value' => $value, ':name' => $setting])
				->execute();
		}
		else
		{
			DC::forge()->insert(DC::p('preferences'), ['name' => $setting, 'value' => $value]);
		}

		if ($reload)
		{
			return static::load_settings(true);
		}

		return static::$_preferences;
	}


	/**
	 * Save in the preferences table the name/value pairs
	 *
	 * @param array $data name => value
	 */
	public static function submit($data)
	{
		foreach ($data as $name => $value)
		{
			// in case it's an array of values from name="thename[]"
			if(is_array($value))
			{
				// remove also empty values with array_filter
				// but we want to keep 0s
				$value = serialize(array_filter($value, function($var){
					if($var === 0)
						return true;
					return $var;
				}));
			}

			static::set($name, $value, false);
		}

		// reload those preferences
		static::load_settings(true);
	}


	/**
	 * A lazy way to submit the preference panel input, saves some code in controller
	 *
	 * This function runs the custom validation function that uses the $form array
	 * to first run the original FuelPHP validation and then the anonymous
	 * functions included in the $form array. It sets a proper notice for the
	 * admin interface on conclusion.
	 *
	 * @param array $form
	 */
	public static function submit_auto($form)
	{
		if (\Input::post())
		{
			if ( ! \Security::check_token())
			{
				\Notices::set('warning', __('The security token wasn\'t found. Try resubmitting.'));
				return false;
			}

			$post = array();

			foreach (\Input::post() as $key => $item)
			{
				// PHP doesn't allow periods in POST array
				$post[str_replace(',', '.', $key)] = $item;
			}

			$result = \Validation::form_validate($form, $post);
			if (isset($result['error']))
			{
				\Notices::set('warning', $result['error']);
			}
			else
			{
				if (isset($result['warning']))
				{
					\Notices::set('warning', $result['warning']);
				}

				\Notices::set('success', __('Preferences updated.'));
				static::submit($result['success']);
			}
		}
	}

}

/* end of file preferences.php */