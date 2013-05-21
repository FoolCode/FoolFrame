<?php

namespace Foolz\Foolframe\Model;

use Foolz\Config\Config;
use Foolz\Plugin\Hook;
use Monolog\Handler\ChromePHPHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\WebProcessor;
use Symfony\Component\Console\Application;
use Symfony\Component\Debug\Debug;
use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\Debug\ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\EventListener\ResponseListener;

class Framework extends HttpKernel
{
	/**
	 * RouteCollection that stores all of the Framework's Routes set before controllers
	 *
	 * @var \Symfony\Component\Routing\RouteCollection
	 */
	public $routeCollection;

	/**
	 * @var \Monolog\Logger
	 */
	public $logger;

	/**
	 * Called directly from index.php
	 * Starts up the Symfony components and the FoolFrame components
	 */
	public function __construct()
	{
		Debug::enable();
		// there's a mistyped docblock on register(), remove the following when it's fixed
		/** @var  $error_handler \Symfony\Component\Debug\ErrorHandler */
		$error_handler = ErrorHandler::register();
		// this is enabled by Debug::enable()
		// ExceptionHandler::register();

		$this->logger = new Logger('foolframe');
		$this->logger->pushHandler(new RotatingFileHandler(VAPPPATH.'foolz/foolframe/logs/foolframe.log'), 7);
		$error_handler->setLogger($this->logger);
		$this->logger->pushProcessor(new IntrospectionProcessor());
		$this->logger->pushProcessor(new WebProcessor());

		$request = Request::createFromGlobals();
		Uri::setRequest($request);

		$this->setupCache();
		$this->setupClassAliases();

		$this->routeCollection = new RouteCollection();

		$this->loadConfig();

		$context = new RequestContext();
		$matcher = new UrlMatcher($this->routeCollection, $context);
		$resolver = new ControllerResolver();

		$dispatcher = new EventDispatcher();
		$dispatcher->addSubscriber(new RouterListener($matcher, null, $this->logger));
		$dispatcher->addSubscriber(new ResponseListener('UTF-8'));

		parent::__construct($dispatcher, $resolver);

		$this->request = $request;
	}

	public function handleWeb()
	{
		$request = $this->request;

		try
		{
			$response = $this->handle($request);
		}
		catch (NotFoundHttpException $e)
		{
			$controller_404 = $this->routeCollection->get('404')->getDefault('_controller');
			$request = new Request();
			$request->attributes->add(['_controller' => $controller_404]);
			$response = $this->handle($request);
		}

		$response->send();
	}

	public function handleConsole()
	{
		$application = new Application();

		Hook::forge('Foolz\Foolframe\Model\Framework::handleConsole.add')
			->setParam('application', $application)
			->setObject($this)
			->execute();

		//$application->add(new \Your\Class\Command\Console()); // that extends Command
		$application->run();
	}

	/**
	 * Allows managing the routes
	 *
	 * @return RouteCollection
	 */
	public function getRouteCollection()
	{
		return $this->routeCollection;
	}

	protected function setupCache()
	{
		// start up the caching system
		$caching_config = Config::get('foolz/foolframe', 'cache', '');
		switch ($caching_config['type'])
		{
			case 'apc':
				$apc_config = \Foolz\Cache\Config::forgeApc();
				$apc_config->setFormat($caching_config['format']);
				$apc_config->setPrefix($caching_config['prefix']);
				$apc_config->setThrow(true);
				\Foolz\Cache\Cache::instantiate($apc_config);
				break;

			case 'memcached':
				$mem_config = \Foolz\Cache\Config::forgeMemcached();
				$mem_config->setFormat($caching_config['format']);
				$mem_config->setPrefix($caching_config['prefix']);
				$mem_config->setServers($caching_config['servers']);
				$mem_config->setThrow(true);
				\Foolz\Cache\Cache::instantiate($mem_config);
				break;

			case 'dummy':
				$dummy_config = \Foolz\Cache\Config::forgeDummy();
				$dummy_config->setFormat($caching_config['format']);
				$dummy_config->setPrefix($caching_config['prefix']);
				$dummy_config->setThrow(true);
				\Foolz\Cache\Cache::instantiate($dummy_config);
				break;
		}
	}

	protected function setupClassAliases()
	{
		class_alias('Foolz\\Foolframe\\Model\\Uri', 'Uri');
		class_alias('Foolz\\Foolframe\\Model\\DoctrineConnection', 'DoctrineConnection');
		class_alias('Foolz\\Foolframe\\Model\\Notices', 'Notices');
		class_alias('Foolz\\Foolframe\\Model\\Plugins', 'Plugins');
		class_alias('Foolz\\Foolframe\\Model\\Preferences', 'Preferences');
		class_alias('Foolz\\Foolframe\\Model\\SchemaManager', 'SchemaManager');
		class_alias('Foolz\\Foolframe\\Model\\System', 'System');
		class_alias('Foolz\\Foolframe\\Model\\User', 'User');
		class_alias('Foolz\\Foolframe\\Model\\Users', 'Users');
	}

	protected function loadConfig()
	{
		//\Module::load('foolz/foolframe', VENDPATH.'foolz/foolframe/');

		// check if FoolFrame is installed and in case it's not, allow reaching install
		if ( ! Config::get('foolz/foolframe', 'config', 'install.installed'))
		{
			\Module::load('install', PKGPATH.'foolz/install/');
			require PKGPATH.'foolz/install/bootstrap.php';
		}
		else
		{/*
			// load each FoolFrame module, bootstrap and config
			foreach(Config::get('foolz/foolframe', 'config', 'modules.installed') as $module)
			{
				// foolframe is already loaded
				if ($module !== 'foolz/foolframe')
				{
				  \Module::load($module, VENDPATH.$module.'/');
				}
			}*/

			$frameworks = [];

			// run the Framework class for each module
			foreach(Config::get('foolz/foolframe', 'config', 'modules.installed') as $module)
			{
				if ($module !== 'foolz/foolframe')
				{
					$class_arr = explode('/', $module);
					$class = '\\';
					foreach ($class_arr as $str)
					{
						$class .= ucfirst($str).'\\';
					}

					$class .= 'Model\Framework';
					$frameworks[] = new $class($this);
				}
			}

			$available_langs = Config::get('foolz/foolframe', 'package', 'preferences.lang.available');
			$lang = \Cookie::get('language');

			if( ! $lang || ! array_key_exists($lang, $available_langs))
			{
				$lang = \Preferences::get('foolframe.lang.default');
			}

			$locale = $lang . '.utf8';
			putenv('LANG=' . $locale);
			putenv('LANGUAGE=' . $locale);
			if ($locale !== "tr_TR.utf8") // long standing PHP bug
			{
				setlocale(LC_ALL, $locale);
			}
			else // workaround to make turkish work
			{
				setlocale(LC_COLLATE, $locale);
				setlocale(LC_MONETARY, $locale);
				setlocale(LC_NUMERIC, $locale);
				setlocale(LC_TIME, $locale);
				setlocale(LC_MESSAGES, $locale);
				setlocale(LC_CTYPE, "sk_SK.utf8");
			}

			bindtextdomain($lang, DOCROOT . "assets/locale");
			bind_textdomain_codeset($lang, 'UTF-8');
			textdomain($lang);

			Plugins::instantiate($this);

			// we must run this later else the plugins have no change to correctly set the
			foreach ($frameworks as $framework)
			{
				$framework->routes();
			}

			foreach(['account', 'plugins', 'preferences', 'system', 'users'] as $location)
			{
				$this->routeCollection->add(
					'foolframe.admin.'.$location, new Route(
						'/admin/'.$location.'/{_suffix}',
						[
							'_suffix' => '',
							'_controller' => '\Foolz\Foolframe\Controller\Admin\\'.ucfirst($location).'::*',
						],
						[
							'_suffix' => '.*',
						]
					)
				);
			}

			$this->routeCollection->add(
				'foolframe.admin', new Route(
					'/admin/{_suffix}',
					[
						'_suffix' => '',
						'_controller' => '\Foolz\Foolframe\Controller\Admin::*'
					],
					[
						'_suffix' => '.*',
					]
				)
			);
		}
	}
}