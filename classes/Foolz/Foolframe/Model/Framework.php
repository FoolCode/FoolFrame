<?php

namespace Foolz\Foolframe\Model;

use Foolz\Config\Config;
use Foolz\Plugin\Hook;
use Foolz\Foolframe\Model\ExceptionHandler;
use Foolz\Profiler\Profiler;
use Monolog\Handler\ChromePHPHandler;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\WebProcessor;
use Symfony\Component\Console\Application;
use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\HttpFoundation\Session\Session;
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
	 * @var \Monolog\Logger
	 */
	public $logger_trace;

	/**
	 * @var Session
	 */
	public $session;

	/**
	 * @var ErrorHandler
	 */
	public $error_handler;

	/**
	 * @var ExceptionHandler
	 */
	public $exception_handler;

    /**
     * @var Profiler
     */
    public $profiler;

	/**
	 * Called directly from index.php
	 * Starts up the Symfony components and the FoolFrame components
	 */
	public function __construct()
	{
		$this->logger = new Logger('foolframe');
		$this->logger->pushHandler(new RotatingFileHandler(VAPPPATH.'foolz/foolframe/logs/foolframe.log', 7, Logger::WARNING));
		$this->logger->pushProcessor(new IntrospectionProcessor());
		$this->logger->pushProcessor(new WebProcessor());

		// special logger that saves stack traces from the exception handler
		$this->logger_trace = new Logger('foolframe_trace');
		$this->logger_trace->pushHandler(new RotatingFileHandler(VAPPPATH.'foolz/foolframe/logs/foolframe_trace.log', 7, Logger::WARNING));
		$this->logger_trace->pushProcessor(new IntrospectionProcessor());
		$this->logger_trace->pushProcessor(new WebProcessor());

		// there's a mistyped docblock on register(), remove the following when it's fixed
		if ('cli' !== php_sapi_name())
		{
            /** @var  $this->error_handler \Symfony\Component\Debug\ErrorHandler */
			error_reporting(-1);
			$this->error_handler = ErrorHandler::register();
			$this->error_handler->setLogger($this->logger_trace);
			$this->exception_handler = ExceptionHandler::register(false);
			$this->exception_handler->setLogger($this->logger);
			$this->exception_handler->setLoggerTrace($this->logger_trace);
		}
		elseif (!ini_get('log_errors') || ini_get('error_log'))
		{
			ini_set('display_errors', 1);
		}

		$request = Request::createFromGlobals();
		Uri::setRequest($request);

        class_alias('\Foolz\Foolframe\Model\Profiler', 'Profiler');
        $this->profiler = new Profiler();
        \Profiler::forge($this->profiler);

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
		// the session isn't actually started until a read/write happens
		$this->session = new Session();
		\Notices::init($this->session);

        // actually start up the profiler if it's an admin browsing
        if (!$this->profiler->isEnabled()) {
            if ($this->session->get('can_see_profiler')) {
                $this->profiler->pushHandler(new ChromePHPHandler());
                $this->profiler->pushHandler(new FirePHPHandler());
                $this->profiler->enable();
            }
        }

        if (\Auth::has_access('maccess.admin')) {
            $this->session->set('can_see_profiler', true);
        }

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
		// check if FoolFrame is installed and in case it's not, allow reaching install
		if ( ! Config::get('foolz/foolframe', 'config', 'install.installed'))
		{
			$this->routeCollection->add(
				'foolframe.install', new Route(
					'/install/{_suffix}',
					[
						'_suffix' => '',
						'_controller' => '\Foolz\Foolframe\Controller\Install::*'
					],
					[
						'_suffix' => '.*',
					]
				)
			);

			$this->routeCollection->add('foolframe.install.index', new Route(
				'/',
				['_controller' => '\Foolz\Foolframe\Controller\Install::index']
			));

			$this->routeCollection->add('404', new Route(
				'',
				['_controller' => '\Foolz\Foolframe\Controller\Install::404']
			));
		}
		else
		{
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

			$locale = $lang.'.utf8';
			putenv('LANG='.$locale);
			putenv('LANGUAGE='.$locale);
			setlocale(LC_ALL, $locale);
			bindtextdomain($lang, DOCROOT."assets/locale");
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