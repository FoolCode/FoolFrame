<?php

namespace Foolz\Foolframe\Model;


class ThemeException extends \Exception {}
class ThemeModuleNotSelectedException extends ThemeException {}
class ThemeFileNotFoundException extends ThemeException {}

/**
 * FoOlFrame Theme Model
 *
 * The Theme Model puts together the public interface. It allows fallback
 * a-la-wordpress child themes. It also allows using the Plugin Model to
 * fully costumize controller and models for each theme.
 *
 * @package        	FoOlFrame
 * @subpackage    	Models
 * @category    	Models
 * @author        	FoOlRulez
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html
 */
class Theme extends \Model
{

	/**
	 * The theme configurations that are loaded
	 *
	 * @var array associative array('theme_dir' => array('item' => 'value'));
	 */
	private $_loaded = array();

	/**
	 * If get_all() was used, this will be true and themes won't be checked again
	 *
	 * @var array
	 */
	private $_is_all_loaded = false;

	/**
	 * The name of the selected module
	 *
	 * @var string|bool the name of the module
	 */
	private $_selected_module = false;

	/**
	 * The name of the selected theme
	 *
	 * @var string|bool the folder name of the theme or FALSE if not set
	 */
	private $_selected_theme = false;

	/**
	 * The version of the selected theme
	 *
	 * @var string the version number of the theme
	 */
	private $_selected_theme_version = null;

	/**
	 * The selected layout
	 *
	 * @var string|bool FALSE when not choosen
	 */
	private $_selected_layout = false;

	/**
	 * The selected partials
	 *
	 * @var array keys as the name of the partial and the value an array of set variables
	 */
	private $_selected_partials = array();

	/**
	 * Variables available to all views
	 *
	 * @var array
	 */
	private $_view_variables = array();

	/**
	 * The string separating pieces of the <title>
	 *
	 * @var string
	 */
	private $_title_separator = ' » ';

	/**
	 * The breadcrumbs of which the title is composed
	 *
	 * @var array
	 */
	private $_title = array();

	/**
	 * The lines of metadata to print
	 *
	 * @var array
	 */
	private $_metadata = array();

	/**
	 * Array of named instances to grab the objects anywhere
	 *
	 * @var array
	 */
	private static $_instances = array();

	/**
	 * Currently active instance
	 *
	 * @var Theme
	 */
	private static $_set_instance = null;


	public static function forge($name = 'default')
	{
		static::$_set_instance = $name;
		return static::$_instances[$name] = new static();
	}

	public static function instance($name = null)
	{
		if ($name === null)
		{
			$name = static::$_set_instance;
		}

		static::$_set_instance = $name;
		return static::$_instances[$name];
	}

	public function getMod()
	{
		//return str_replace('foolz/', '', $this->_selected_module);
		return $this->_selected_module;
	}

	/**
	 * Returns all the themes available and saves the array in a variable
	 *
	 * @return array array with the theme name as index, and their config as value
	 */
	public function get_all()
	{
		\Profiler::mark('Start Theme::get_all');
		\Profiler::mark_memory($this, 'Start Theme::get_all');

		if ($this->_is_all_loaded)
			return $this->_loaded;

		$array = array();

		foreach ($this->get_all_names() as $name)
		{
			$array[$name] = $this->load_config($name);
		}

		$this->_is_all_loaded = true;
		$this->_loaded = $array;

		return $array;

		\Profiler::mark('End Theme::get_all');
		\Profiler::mark_memory($this, 'End Theme::get_all');
	}


	/**
	 * Get the config array of a single theme
	 *
	 * @param type $name
	 * @return type
	 */
	public function get_by_name($name)
	{
		if (isset($this->_loaded[$name]))
			return $this->_loaded[$name];

		$this->_loaded[$name] = $this->load_config($name);

		return $this->_loaded[$name];
	}


	/**
	 * Returns an array of key => value of the available themes
	 */
	public function get_available_themes()
	{
		\Profiler::mark('Start Theme::get_available_themes');
		\Profiler::mark_memory($this, 'Start Theme::get_available_themes');

		if (\Auth::has_access('maccess.mod'))
		{
			// admins get all the themes
			return array_keys($this->get_all());
		}
		else
		{
			$active_themes = Preferences::get(\Foolz\Config\Config::get($this->_selected_module, 'package','main.identifier').'.theme.active_themes');
			if ( ! $active_themes || ! $active_themes = @unserialize($active_themes))
			{
				// default WORKING themes coming with the application
				return array(
					'default'
				);
			}
			else
			{
				foreach ($active_themes as $key => $enabled)
				{
					if (!$enabled)
					{
						unset($active_themes[$key]);
					}
				}

				return $active_themes = array_keys($active_themes);
			}
		}

		\Profiler::mark('End Theme::get_available_themes');
		\Profiler::mark_memory($this, 'End Theme::get_available_themes');
	}


	public function get_available_styles($theme)
	{
		$theme = $this->get_by_name($theme);

		return (isset($theme['styles'])) ? $theme['styles'] : array();
	}


	/**
	 * Gets a config setting from the selected theme
	 *
	 * @param type $name
	 */
	public function get_selected_theme()
	{
		return $this->_selected_theme;
	}


	public function get_selected_theme_version()
	{
		return $this->_selected_theme_version;
	}


	public function get_selected_theme_class($class = array())
	{
		if ($theme_styles = $this->get_available_styles($this->_selected_theme))
		{
			$style = \Cookie::get('theme_'.$this->_selected_theme.'_style');
			if ($style !== false && in_array($style, $theme_styles))
				$class[] = $style;
			else
				$class[] = current($theme_styles);
		}

		return implode(' ', $class);
	}


	/**
	 * Gets a config setting from the selected theme
	 *
	 * @param type $name
	 */
	public function get_config($name)
	{
		if (isset($this->_loaded[$this->_selected_theme][$name]))
		{
			return $this->_loaded[$this->_selected_theme][$name];
		}
		else
		{
			return false;
		}
	}


	/**
	 * Browses the theme directory and grabs all the folder names
	 *
	 * @return type
	 */
	public function get_all_names()
	{
		\Profiler::mark('Start Theme::get_all_names');
		\Profiler::mark_memory($this, 'Start Theme::get_all_names');

		$array = array();

		if ($handle = opendir(VENDPATH.$this->getMod().'/public/themes/'))
		{
			while (false !== ($file = readdir($handle)))
			{
				if (in_array($file, array('..', '.')))
					continue;

				if (is_dir(VENDPATH.$this->getMod().'/public/themes/'.$file))
				{
					$array[] = $file;
				}
			}
			closedir($handle);
		}

		\Profiler::mark('End Theme::get_available_themes');
		\Profiler::mark_memory($this, 'End Theme::get_available_themes');

		return $array;
	}


	/**
	 * Opens theme_config and grabs the $config
	 *
	 * @param string $name the folder name of the theme
	 * @return array the config array or FALSE if not found
	 */
	private function load_config($name)
	{
		\Profiler::mark('Start Theme::load_config');
		\Profiler::mark_memory($this, 'Start Theme::load_config');

		return \Fuel::load(VENDPATH.$this->getMod().'/public/themes/'.$name.'/config.php');

		\Profiler::mark('End Theme::load_config');
		\Profiler::mark_memory($this, 'End Theme::load_config');
	}


	public function set_module($module = false)
	{
		return $this->_selected_module = $module;
	}


	/**
	 * Checks if the theme is available to the rules and loads it
	 *
	 * @param string $theme
	 * @return array the theme config
	 */
	public function set_theme($theme)
	{
		\Profiler::mark('Start Theme::load_config');
		\Profiler::mark_memory($this, 'Start Theme::load_config');

		if (is_null($this->_selected_module))
		{
			throw new ThemeModuleNotSelectedException;
		}

		// sending FALSE leaves the situation unchanged
		if ($theme === false)
		{
			return false;
		}

		if ( ! in_array($theme, $this->get_available_themes()))
		{
			$theme = \Preferences::get(\Foolz\Config\Config::get($this->_selected_module, 'package','main.identifier').'.themes.default');
		}

		$result = $this->get_by_name($theme);
		$this->_selected_theme = $theme;
		$this->_selected_theme_version = $result['version'];

		// load the theme bootstrap file if present
		\Fuel::load(VENDPATH.$this->getMod().'/public/themes/'.$theme.'/bootstrap.php');

		\Profiler::mark('End Theme::load_config');
		\Profiler::mark_memory($this, 'End Theme::load_config');

		return $result;
	}


	/**
	 * Selects the layout to use
	 *
	 * @param string $layout the filename without .php extension
	 */
	public function set_layout($layout)
	{
		$this->_selected_layout = $layout;
	}


	/**
	 * Sets a partial view
	 *
	 * @param type $partial
	 * @param type $data
	 */
	public function set_partial($name, $partial, $data = array())
	{
		$this->_selected_partials[$name] = array('partial' => $partial, 'data' => $data);
	}


	/**
	 * Sets a variable that is globally avariable through layout and partials
	 *
	 * @param type $name
	 * @param type $value
	 */
	public function bind($name, $value = null)
	{
		if(is_array($name))
		{
			foreach($name as $key => $val)
			{
				$this->bind($key, $val);
			}

			return $this;
		}

		$this->_view_variables[$name] = $value;

		return $this;
	}


	/**
	 * Unsets a variable that is globally avariable through layout and partials
	 *
	 * @param string $name
	 * @param any $value
	 */
	public function unbind($name, $value)
	{
		unset($this->_view_variables[$name]);

		return $this;
	}


	public function get_var($name)
	{
		return $this->_view_variables[$name];
	}


	/**
	 * Adds breadcrumbs to the title
	 *
	 * @param string|array if array it will set the title array from scratch
	 * @return array the title array
	 */
	public function set_title($title)
	{
		if (is_array($title))
			$this->_title = $title;
		else
			$this->_title[] = $title;

		return $this->_title;
	}


	/**
	 * Adds metadata to header
	 *
	 * @param string|array if array it will set the metadata array from scratch
	 * @return array the metadata array
	 */
	public function set_metadata($metadata)
	{
		if (is_array($metadata))
			$this->_metadata = $metadata;
		else
			$this->_metadata[] = $metadata;

		return $this->_metadata;
	}


	/**
	 * Provides the path to the asset and in case its fallback.
	 *
	 * @param type $asset the location of the asset with theme folder as root
	 * @return string The location of the asset in the theme folder
	 */
	public function fallback_asset($asset)
	{
		$asset = ltrim($asset, '/');

		$version = $this->_selected_theme_version;
		if (file_exists(DOCROOT.$this->getMod().'/themes/'.$this->_selected_theme.'/'.$asset))
		{
			return $this->getMod().'/themes/'.$this->_selected_theme.'/'.$asset.'?v='.$version;
		}
		else
		{
			return $this->getMod().'/themes/'.$this->get_config('extends').'/'.$asset.'?v='.$version;
		}
	}


	/**
	 * This function is used in case of CSS files. A child theme may load both its
	 * and the parent theme CSS.
	 *
	 * @param string $asset the location of the asset with theme folder as root
	 * @return array the paths to each file in the overriding order
	 */
	public function fallback_override($asset, $double = false)
	{
		// if we aren't going to have stuff like two CSS overrides, return the theme's file
		if (!$double || $this->get_config('extends') == $this->_selected_theme)
		{
			return array($this->fallback_asset($asset));
		}

		$version = $this->_selected_theme_version;
		$result = array();
		if (file_exists(VENDPATH.$this->getMod().'/public/themes/'.$this->get_config('extends').'/'.$asset))
			$result[] = $this->getMod().'/themes/'.$this->get_config('extends').'/'.$asset.'?v='.$version;

		if (file_exists(VENDPATH.$this->getMod().'/public/themes/'.$this->_selected_theme.'/'.$asset))
			$result[] = $this->getMod().'/public/themes/'.$this->_selected_theme.'/'.$asset.'?v='.$version;

		// we want first extended theme and then the override
		return $result;
	}


	/**
	 * Wraps up all the choices and returns or outputs the HTML
	 *
	 * @param string $view the content to insert in the layout
	 * @param array $data key value instead of using bind()
	 * @param bool $return TRUE to return the HTML as string
	 * @return string the HTML
	 */
	public function build($view, $data = array(), $without_layout = false)
	{
		\Profiler::mark('Theme::build Start');
		foreach ($data as $key => $item)
		{
			$this->bind($key, $item);
		}

		// build the partials
		$partials = array();
		foreach ($this->_selected_partials as $name => $partial)
		{
			$partials[$name] = $this->_build(
				$partial['partial'], 'partial', array_merge($this->_view_variables, $partial['data'])
			);
		}

		// build the content that goes in the middle
		$content = $this->_build(
			$view, 'content', array_merge($this->_view_variables, array('template' => array('partials' => $partials)))
		);

		// if there's no selected layout output or return this
		if ($without_layout || $this->_selected_layout === false)
		{
			\Profiler::mark_memory($content, 'Theme $content');
			\Profiler::mark('Theme::build End without layout');
			return $content;
		}

		// build the layout
		$html = $this->_build(
			$this->_selected_layout, 'layout',
			array_merge(
				$this->_view_variables,
				array('template' => array(
						'body' => $content,
						'title' => implode($this->_title_separator, $this->_title),
						'partials' => $partials,
						'metadata' => implode("\n", $this->_metadata)
					)
				)
			)
		);

		\Profiler::mark_memory($html, 'Theme $html');
		\Profiler::mark('Theme::build End');
		return $html;
	}


	/**
	 * Merges variables and view and returns the HTML as string
	 *
	 * @param string $file
	 * @param string $type
	 * @param array $data
	 * @return string
	 */
	private function _build($_file, $_type, $_data = array())
	{
		// check if we have a class corresponding the view we looked for
		foreach (array($this->get_selected_theme(), $this->get_config('extends')) as $_directory)
		{
			$class = ucfirst($this->getMod()).'\\Themes\\'
				.($_directory === 'default' ?'Default_' : ucfirst($_directory)).'\\Views\\'.ucfirst($_file);
			if (class_exists($class))
			{
				return (string) new $class($_data, $this);
			}
		}

		unset($class, $view);

		foreach (array($this->get_selected_theme(), $this->get_config('extends')) as $_directory)
		{
			switch ($_type)
			{
				case 'layout':
					if (file_exists(VENDPATH.$this->getMod().'/public/themes/'.$_directory.'/views/layouts/'.$_file.'.php'))
					{
						$_location = VENDPATH.$this->getMod().'/public/themes/'.$_directory.'/views/layouts/'.$_file.'.php';
					}
					break;
				case 'content':
				case 'partial':
					if (file_exists(VENDPATH.$this->getMod().'/public/themes/'.$_directory.'/views/'.$_file.'.php'))
					{
						$_location = VENDPATH.$this->getMod().'/public/themes/'.$_directory.'/views/'.$_file.'.php';
					}
					break;
			}
			if (isset($_location))
				break;
		}

		if ( ! isset($_location))
		{
			throw new ThemeFileNotFoundException;
		}

		// get rid of interfering variables
		unset($_type, $_file);

		extract($_data);

		ob_start();

		// rewrite short tags from CodeIgniter 2.1
		if (version_compare(phpversion(), '5.4.0') < 0 && (bool) @ini_get('short_open_tag') === false)
		{
			try
			{
				$_tagged = \Cache::get('model.theme._build.view.'.str_replace(array('/', '\\'), array('.', '.'), $_location));
			}
			catch (\CacheNotFoundException $e)
			{
				$_tagged = '?>'.preg_replace("/;*\s*\?>/", "; ?>", str_replace('<?=', '<?php echo ', file_get_contents($_location)));
				\Cache::set('model.theme._build.view.'.str_replace(array('/', '\\'), array('.', '.'), $_location), $_tagged, 2);
			}
			echo eval($_tagged);
		}
		else
		{
			include $_location;
		}

		$string = ob_get_clean();
		return $string;
	}

}

/* end of file theme.php */
