<?php

namespace Foolz\FoolFrame\Model;

use Foolz\Cache\Cache;
use Foolz\FoolFrame\Model\Auth\WrongKeyException;
use Foolz\Plugin\Hook;
use Foolz\Profiler\Profiler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger as MonoLogger;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\WebProcessor;
use Symfony\Component\Console\Application;
use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\DependencyInjection\Reference;
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
use Symfony\Component\DependencyInjection\ContainerBuilder;

class Context implements ContextInterface
{
    /**
     * @var HttpKernel
     */
    protected $http_kernel;

    /**
     * @var ContainerBuilder
     */
    protected $container;

    /**
     * @var ContextInterface[]
     */
    protected $child_contextes = [];

    /**
     * RouteCollection that stores all of the Framework's Routes set before controllers
     *
     * @var \Symfony\Component\Routing\RouteCollection
     */
    protected $route_collection;

    /**
     * @var \Monolog\Logger
     */
    protected $logger;

    /**
     * @var \Monolog\Logger
     */
    protected $logger_trace;

    /**
     * @var ErrorHandler
     */
    protected $error_handler;

    /**
     * @var ExceptionHandler
     */
    protected $exception_handler;

    /**
     * @var Profiler
     */
    protected $profiler;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Preferences
     */
    protected $preferences;

    public function __sleep()
    {
        return array();
    }

    /**
     * Called directly from index.php
     * Starts up the Symfony components and the FoolFrame components
     */
    public function __construct()
    {
        $this->container = new ContainerBuilder();

        $this->container->register('profiler', 'Foolz\Profiler\Profiler')
            ->addMethodCall('enable', []);

        $this->profiler = $this->container->get('profiler');

        class_alias('Foolz\FoolFrame\Model\Plugins', 'Plugins');
        class_alias('Foolz\FoolFrame\Model\SchemaManager', 'SchemaManager');
        class_alias('Foolz\FoolFrame\Model\System', 'System');
        class_alias('Foolz\FoolFrame\Model\User', 'User');
        class_alias('Foolz\FoolFrame\Model\Users', 'Users');

        $this->route_collection = new RouteCollection();

        $this->logger = new MonoLogger('foolframe');
        $this->logger->pushHandler(new RotatingFileHandler(VAPPPATH.'foolz/foolframe/logs/foolframe.log', 7, MonoLogger::WARNING));
        $this->logger->pushProcessor(new IntrospectionProcessor());
        $this->logger->pushProcessor(new WebProcessor());

        // special logger that saves stack traces from the exception handler
        $this->logger_trace = new MonoLogger('foolframe_trace');
        $this->logger_trace->pushHandler(new RotatingFileHandler(VAPPPATH.'foolz/foolframe/logs/foolframe_trace.log', 7, MonoLogger::WARNING));
        $this->logger_trace->pushProcessor(new IntrospectionProcessor());
        $this->logger_trace->pushProcessor(new WebProcessor());

        if ('cli' !== php_sapi_name()) {
            error_reporting(-1);
            $this->error_handler = ErrorHandler::register();
            $this->error_handler->setLogger($this->logger_trace);
            $this->exception_handler = ExceptionHandler::register(false);
            $this->exception_handler->setLogger($this->logger);
            $this->exception_handler->setLoggerTrace($this->logger_trace);
        } elseif (!ini_get('log_errors') || ini_get('error_log')) {
            ini_set('display_errors', 1);
        }

        $this->container->register('autoloader', 'Foolz\FoolFrame\Model\Autoloader')
            ->addArgument($this)
            ->addMethodCall('register');

        $this->container->register('logger', 'Foolz\FoolFrame\Model\Logger')
            ->addArgument($this)
            ->addMethodCall('addLogger', [$this->logger])
            ->addMethodCall('addLogger', [$this->logger_trace]);

        $this->container->register('config', 'Foolz\FoolFrame\Model\Config')
            ->addArgument($this);

        $this->container->register('doctrine', 'Foolz\FoolFrame\Model\DoctrineConnection')
            ->addArgument($this)
            ->addArgument(new Reference('config'));

        $this->container->register('mailer', 'Foolz\FoolFrame\Model\Mailer')
            ->addArgument($this);

        $this->container->register('preferences', 'Foolz\FoolFrame\Model\Preferences')
            ->addArgument($this);

        $this->container->register('plugins', 'Foolz\FoolFrame\Model\Plugins')
            ->addArgument($this);

        $this->container->register('users', 'Foolz\FoolFrame\Model\Users')
            ->addArgument($this);

        $this->container->register('auth', 'Foolz\FoolFrame\Model\Auth')
            ->addArgument($this);

        $this->container->register('security', 'Foolz\FoolFrame\Model\Security')
            ->addArgument($this);

        $this->config = $this->getService('config');
        $this->preferences = $this->getService('preferences');

        // start up the caching system
        $caching_config = $this->config->get('foolz/foolframe', 'cache', '');
        switch ($caching_config['type']) {
            case 'redis':
                $mem_config = \Foolz\Cache\Config::forgeRedis();
                $mem_config->setFormat($caching_config['format']);
                $mem_config->setPrefix($caching_config['prefix']);
                $mem_config->setServers($caching_config['servers']);
                $mem_config->setThrow(true);
                Cache::instantiate($mem_config);
                break;

            case 'memcached':
                $mem_config = \Foolz\Cache\Config::forgeMemcached();
                $mem_config->setFormat($caching_config['format']);
                $mem_config->setPrefix($caching_config['prefix']);
                $mem_config->setServers($caching_config['servers']);
                $mem_config->setThrow(true);
                Cache::instantiate($mem_config);
                break;

            case 'apc':
                $apc_config = \Foolz\Cache\Config::forgeApc();
                $apc_config->setFormat($caching_config['format']);
                $apc_config->setPrefix($caching_config['prefix']);
                $apc_config->setThrow(true);
                Cache::instantiate($apc_config);
                break;

            case 'dummy':
                $dummy_config = \Foolz\Cache\Config::forgeDummy();
                $dummy_config->setFormat($caching_config['format']);
                $dummy_config->setPrefix($caching_config['prefix']);
                $dummy_config->setThrow(true);
                Cache::instantiate($dummy_config);
                break;
        }

        // run the Framework class for each module
        foreach($this->config->get('foolz/foolframe', 'config', 'modules.installed') as $module) {
            if ($module['namespace'] !== 'foolz/foolframe') {
                $context = $module['context'];
                $this->child_contextes[$module['namespace']] = new $context($this);
            }
        }

        $this->profiler->log('Start Plugin instantiation');
        if (count($this->child_contextes)) {
            $this->getService('plugins')->instantiate();
        }
        $this->profiler->log('Stop Plugin instantiation');
    }

    /**
     * @param $service
     * @return object
     */
    public function getService($service)
    {
        return $this->container->get($service);
    }

    public function handleWeb(Request $request = null)
    {
        if ($request === null) {
            // create the request from the globals if we don't have custom input
            $request = Request::createFromGlobals();
        }

        $this->container
            ->register('uri', '\Foolz\FoolFrame\Model\Uri')
            ->addArgument($this)
            ->addArgument($request);

        $remember_me = $request->cookies->get(
            $this->config->get('foolz/foolframe', 'config', 'config.cookie_prefix').'rememberme'
        );

        if (!count($this->child_contextes)) {
            // no app installed, we need to go to the install
            $this->loadInstallRoutes($this->route_collection);
        } else {
            $this->profiler->log('Start Auth rememberme');
            /** @var Auth $auth */
            $auth = $this->getService('auth');
            if ($remember_me) {
                try {
                    $auth->authenticateWithRememberMe($remember_me);
                } catch (WrongKeyException $e) {
                }
            }
            $this->profiler->log('Stop Auth rememberme');

            Hook::forge('Foolz\FoolFrame\Model\Context::handleWeb#obj.afterAuth')
                ->setObject($this)
                ->setParam('route_collection', $this->route_collection)
                ->execute();

            $this->profiler->log('Start Plugins handleWeb');
            $this->getService('plugins')->handleWeb();
            $this->profiler->log('Stop Plugins handleWeb');

            $this->profiler->log('Start language setup');
            $available_langs = $this->config->get('foolz/foolframe', 'package', 'preferences.lang.available');
            $lang = $request->cookies->get('language');

            if(!$lang || !array_key_exists($lang, $available_langs)) {
                $lang = $this->preferences->get('foolframe.lang.default');
            }

            // HHVM can't handle gettext
            if (function_exists('bindtextdomain')) {
                $locale = $lang.'.utf8';
                putenv('LANG='.$locale);
                putenv('LANGUAGE='.$locale);
                setlocale(LC_ALL, $locale);
                bindtextdomain($lang, DOCROOT."assets/locale");
                bind_textdomain_codeset($lang, 'UTF-8');
                textdomain($lang);
            }
            $this->profiler->log('Stop language setup');

            $this->profiler->log('Start routes setup');
            // load the routes from the child contextes first
            Hook::forge('Foolz\FoolFrame\Model\Context::handleWeb#obj.routing')
                ->setObject($this)
                ->setParam('route_collection', $this->route_collection)
                ->execute();

            foreach ($this->child_contextes as $context) {
                $context->handleWeb($request);
                $context->loadRoutes($this->route_collection);
            }
            $this->profiler->log('Stop routes setup');

            $this->profiler->log('Start routes load');
            $this->loadRoutes($this->route_collection);
            $this->profiler->log('Stop routes setup');
        }

        // load the framework routes
        Hook::forge('Foolz\FoolFrame\Model\Context::handleWeb#obj.context')
            ->setObject($this)
            ->execute();

        // this is the first time we know we have a request for sure
        // hooks that need the request to function must run here
        Hook::forge('Foolz\FoolFrame\Model\Context::handleWeb#obj.request')
            ->setObject($this)
            ->setParam('request', $request)
            ->execute();

        $this->container->register('notices', 'Foolz\FoolFrame\Model\Notices')
            ->addArgument($this)
            ->addArgument($request);

        $this->profiler->log('Start HttpKernel loading');

        $request_context = new RequestContext();
        $request_context->fromRequest($request);
        $matcher = new UrlMatcher($this->route_collection, $request_context);
        $resolver = new ControllerResolver($this);
        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(new RouterListener($matcher, null, $this->logger));
        $dispatcher->addSubscriber(new ResponseListener('UTF-8'));
        $this->http_kernel = new HttpKernel($dispatcher, $resolver);

        $this->profiler->log('End HttpKernel loading');

        // if this hook is used, it can override the entirety of the request handling
        $response = Hook::forge('Foolz\FoolFrame\Model\Context::handleWeb#obj.response')
            ->setObject($this)
            ->setParam('request', $request)
            ->execute()
            ->get(false);

        if (!$response) {
            try {
                $this->profiler->log('Request handling start');
                $response = $this->http_kernel->handle($request);
                $this->profiler->log('Request handling end');
            } catch (NotFoundHttpException $e) {
                $controller_404 = $this->route_collection->get('404')->getDefault('_controller');
                $request = new Request();
                $request->attributes->add(['_controller' => $controller_404]);
                $response = $this->http_kernel->handle($request);
            }

            // stick the html of the profiler at the end
            if ($request->getRequestFormat() == 'html' && isset($auth) && $auth->hasAccess('maccess.admin')) {
                $content = explode('</body>', $response->getContent());
                if (count($content) == 2) {
                    $this->profiler->log('Execution end');
                    $response->setContent($content[0].$this->profiler->getHtml().'</body>'.$content[1]);
                }
            }
        }

        $this->getService('security')->updateCsrfToken($response);

        $response->send();
    }

    public function handleConsole()
    {
        $application = new Application();

        Hook::forge('Foolz\FoolFrame\Model\Context::handleConsole#obj.app')
            ->setObject($this)
            ->setParam('application', $application)
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
        return $this->route_collection;
    }

    /**
     * @return ContainerBuilder
     */
    public function getContainer()
    {
        return $this->container;
    }

    protected function loadInstallRoutes(RouteCollection $route_collection)
    {
        $route_collection->add(
            'foolframe.install', new Route(
                '/install/{_suffix}',
                [
                    '_suffix' => '',
                    '_controller' => '\Foolz\FoolFrame\Controller\Install::*'
                ],
                [
                    '_suffix' => '.*',
                ]
            )
        );

        $route_collection->add('foolframe.install.index', new Route(
            '/',
            ['_controller' => '\Foolz\FoolFrame\Controller\Install::index']
        ));

        $route_collection->add('404', new Route(
            '',
            ['_controller' => '\Foolz\FoolFrame\Controller\Install::404']
        ));
    }

    public function loadRoutes(RouteCollection $route_collection)
    {
        foreach(['account', 'plugins', 'preferences', 'system', 'users'] as $location) {
            $route_collection->add(
                'foolframe.admin.'.$location, new Route(
                    '/admin/'.$location.'/{_suffix}',
                    [
                        '_suffix' => '',
                        '_controller' => '\Foolz\FoolFrame\Controller\Admin\\'.ucfirst($location).'::*',
                    ],
                    [
                        '_suffix' => '.*',
                    ]
                )
            );
        }

        $route_collection->add(
            'foolframe.admin', new Route(
                '/admin/{_suffix}',
                [
                    '_suffix' => '',
                    '_controller' => '\Foolz\FoolFrame\Controller\Admin::*'
                ],
                [
                    '_suffix' => '.*',
                ]
            )
        );
    }
}
