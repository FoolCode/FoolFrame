<?php

namespace Foolz\Foolframe\Controller;

use Foolz\Foolframe\Model\Plugins;
use Foolz\Theme\Loader;
use Symfony\Component\HttpFoundation\Request;

class Admin extends Common
{
	protected $_views = null;
	private static $sidebar = [];
	private static $sidebar_dynamic = [];

	/**
	 * @var \Foolz\Theme\Theme
	 */
	protected $theme;

	/**
	 * @var \Foolz\Theme\ParamManager
	 */
	protected $param_manager;

	/**
	 * @var \Foolz\Theme\Builder
	 */
	protected $builder;

    public function before(Request $request)
    {
	    parent::before($request);

	    $segments = explode('/', $request->getPathInfo());
	    $path = '';
	    if (count($segments) > 3)
	    {
		    $path = '/'.$segments[1].'/'.$segments[2].'/'.$segments[3].'/';
	    }

		if ( ! \Auth::has_access('maccess.user') && ! in_array($path,
			['/admin/account/register/', '/admin/account/activate/', '/admin/account/login/',
				'/admin/account/change_password/', '/admin/account/forgot_password/']))
		{
			return \Response::redirect('admin/account/login');
		}

	    $theme_instance = \Foolz\Theme\Loader::forge('foolframe_admin');
	    $theme_instance->addDir(VENDPATH.'foolz/foolframe/public/themes-admin/');
	    $theme_instance->addDir(VAPPPATH.'foolz/foolframe/themes-admin/');
	    $theme_instance->setBaseUrl(\Uri::base().'foolframe/');
	    $theme_instance->setPublicDir(DOCROOT.'foolframe/');
	    // make it possible to override the theme so other framework components can extend with their own
	    $this->setupTheme($theme_instance);
	    $this->builder = $this->theme->createBuilder();
	    $this->param_manager = $this->builder->getParamManager();
	    $this->builder->createLayout('base');


		// returns the hardcoded sidebar array (can't use functions when declaring a class variable)
		self::$sidebar = static::get_sidebar_values();

		// get the plugin sidebars
		self::$sidebar_dynamic = Plugins::getSidebarElements('admin');

		// merge if there were sidebar elements added dynamically
		if ( ! empty(self::$sidebar_dynamic))
		{
			self::$sidebar = self::merge_sidebars(self::$sidebar, self::$sidebar_dynamic);
		}

	    $this->builder->createPartial('navbar', 'navbar');
	    $this->builder->createPartial('sidebar', 'sidebar')
		    ->getParamManager()
		    ->setParams(array('sidebar' => self::get_sidebar($request, self::$sidebar)));
	}

	/**
	 * Selects the theme. Can be overridden so other controllers can use their own admin components
	 *
	 * @param Loader $theme_instance
	 */
	public function setupTheme(Loader $theme_instance)
	{
		$this->theme = $theme_instance->get('foolz/foolframe-theme-admin');
	}

    public function action_index()
    {
		return \Response::redirect('admin/account/profile');
    }

	public function action_404()
	{
		return \Response::forge('404', 404);
	}

	/**
	 * Non-dynamic sidebar array.
	 * Permissions are set inside
	 *
	 * @return sidebar array
	 */
	private static function get_sidebar_values()
	{
		$sidebar = [];

		// load sidebars from modules and leave FoolFrame sidebar on bottom
		foreach(\Foolz\Config\Config::get('foolz/foolframe', 'config', 'modules.installed') as $module)
		{
			$module_sidebar = \Foolz\Config\Config::get($module, 'sidebar');
			if(is_array($module_sidebar))
			{
				$sidebar = array_merge($module_sidebar['sidebar'], $sidebar);
			}
		}

		return $sidebar;
	}

	/**
	 * Sets new sidebar elements, the array must match the defaults' structure.
	 * It can override the methods.
	 *
	 * @param array $array
	 */
	public static function add_sidebar_element($array)
	{
		if (is_null(static::$sidebar_dynamic))
		{
			static::$sidebar_dynamic = [];
		}

		static::$sidebar_dynamic[] = $array;
	}

	/**
	 * Merges without destroying twi sidebars, where $array2 overwrites values of
	 * $array1.
	 *
	 * @param array $array1 sidebar array to be merged into
	 * @param array $array2 sidebar array with elements to merge
	 * @return array resulting sidebar
	 */
	public static function merge_sidebars($array1, $array2)
	{
		// there's a numbered index on the outside!
		foreach ($array2 as $key_top => $item_top)
		{
			foreach($item_top as $key => $item)
			{
				// are we inserting in an already existing method?
				if (isset($array1[$key]))
				{
					// overriding the name
					if (isset($item['name']))
					{
						$array1[$key]['name'] = $item['name'];
					}

					// overriding the permission level
					if (isset($item['level']))
					{
						$array1[$key]['level'] = $item['level'];
					}

					// overriding the default url to reach
					if (isset($item['default']))
					{
						$array1[$key]['default'] = $item['default'];
					}

					// overriding the default url to reach
					if (isset($item['icon']))
					{
						$array1[$key]['icon'] = $item['icon'];
					}

					// adding or overriding the inner elements
					if (isset($item['content']))
					{
						if (isset($array1[$key]['content']))
						{
							$array1[$key]['content'] = self::merge_sidebars($array1[$key]['content'], $item);
						}
						else
						{
							$array1[$key]['content'] = self::merge_sidebars([], $item);
						}
					}
				}
				else
				{
					// the element doesn't exist at all yet
					// let's trust the plugin creator in understanding the structure
					// extra control: allow him to put the plugin after or before any function
					if (isset($item['position']) && is_array($item['position']))
					{
						$before = $item['position']['beforeafter'] == 'before' ? true : false;
						$element = $item['position']['element'];

						$array_temp = $array1;
						$array1 = [];
						foreach ($array_temp as $subkey => $temp)
						{
							if ($subkey == $element)
							{
								if ($before)
								{
									$array1[$key] = $item;
									$array1[$subkey] = $temp;
								}
								else
								{
									$array1[$subkey] = $temp;
									$array1[$key] = $item;
								}

								unset($array_temp[$subkey]);

								// flush the rest
								foreach ($array_temp as $k => $t)
								{
									$array1[$k] = $t;
								}

								break;
							}
							else
							{
								$array1[$subkey] = $temp;
								unset($array_temp[$subkey]);
							}
						}
					}
					else
					{
						$array1[$key] = $item;
					}
				}
			}
		}

		return $array1;
	}

	/**
	 * Returns the sidebar array
	 *
	 * @todo comment this
	 */
	public static function get_sidebar(Request $request, $array)
	{
		$segments = explode('/', $request->getPathInfo());

		// not logged in users don't need the sidebar
		if (\Auth::member('guest'))
		{
			return [];
		}

		$result = [];
		foreach ($array as $key => $item)
		{
			if (\Auth::has_access('maccess.' . $item['level']) && ! empty($item))
			{
				$subresult = $item;

				// segment 2 contains what's currently active so we can set it lighted up
				if (isset($segments[2]) && $segments[2] == $key)
				{
					$subresult['active'] = true;
				}
				else
				{
					$subresult['active'] = false;
				}

				// we'll cherry-pick the content next
				unset($subresult['content']);

				// recognize plain URLs
				if ((substr($item['default'], 0, 7) == 'http://') ||
					(substr($item['default'], 0, 8) == 'https://'))
				{
					// nothing to do here, just copy the URL
					$subresult['href'] = $item['default'];
				}
				else
				{
					// else these are internal URIs
					// what if it uses more segments or is even an array?
					if (!is_array($item['default']))
					{
						$default_uri = explode('/', $item['default']);
					}
					else
					{
						$default_uri = $item['default'];
					}
					array_unshift($default_uri, 'admin', $key);
					$subresult['href'] = \Uri::create(implode('/', $default_uri));
				}

				$subresult['content'] = [];

				// cherry-picking subfunctions
				foreach ($item['content'] as $subkey => $subitem)
				{
					$subsubresult = $subitem;
					if (\Auth::has_access('maccess.' . $subitem['level']))
					{
						if ($subresult['active'] && (isset($segments[2]) && $segments[3] == $subkey ||
							(
							isset($subitem['alt_highlight']) &&
							in_array($segments[3], $subitem['alt_highlight'])
							)
							))
						{
							$subsubresult['active'] = true;
						}
						else
						{
							$subsubresult['active'] = false;
						}

						// recognize plain URLs
						if ((substr($subkey, 0, 7) == 'http://') ||
							(substr($subkey, 0, 8) == 'https://'))
						{
							// nothing to do here, just copy the URL
							$subsubresult['href'] = $subkey;
						}
						else
						{
							// else these are internal URIs
							// what if it uses more segments or is even an array?
							if (!is_array($subkey))
							{
								$default_uri = explode('/', $subkey);
							}
							else
							{
								$default_uri = $subkey;
							}
							array_unshift($default_uri, 'admin', $key);
							$subsubresult['href'] = \Uri::create(implode('/', $default_uri));
						}

						$subresult['content'][] = $subsubresult;
					}
				}

				$result[] = $subresult;
			}
		}
		return $result;
	}
}
