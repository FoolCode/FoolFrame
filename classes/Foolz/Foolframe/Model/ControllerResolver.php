<?php

namespace Foolz\Foolframe\Model;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ControllerResolver extends \Symfony\Component\HttpKernel\Controller\ControllerResolver
{
    private $logger;

    /**
     * Holds parameters taken from the _suffix
     *
     * @var null|array
     */
    private $parameters = null;

    /**
     * Constructor.
     *
     * @param LoggerInterface $logger A LoggerInterface instance
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * Returns the Controller instance associated with a Request.
     *
     * This method looks for a '_controller' request attribute that represents
     * the controller name (a string like ClassName::MethodName).
     *
     * @param Request $request A Request instance
     *
     * @return mixed|Boolean A PHP callable representing the Controller,
     *                       or false if this resolver is not able to determine the controller
     *
     * @throws \InvalidArgumentException|\LogicException If the controller can't be found
     *
     * @api
     */
    public function getController(\Symfony\Component\HttpFoundation\Request $request)
    {
        if (!$controller = $request->attributes->get('_controller')) {
            if (null !== $this->logger) {
                $this->logger->warning('Unable to look for the controller as the "_controller" parameter is missing');
            }

            return false;
        }

        if (is_array($controller) || (is_object($controller) && method_exists($controller, '__invoke'))) {
            return $controller;
        }

        if (false === strpos($controller, ':')) {
            if (method_exists($controller, '__invoke')) {
                return new $controller;
            } elseif (function_exists($controller)) {
                return $controller;
            }
        }

        list($controller, $method) = $this->createController($controller);

        $this->parameters = array();
        if ($suffix = $request->attributes->get('_suffix')) {
            $parameters_temp = explode('/', $suffix);
            $parameters_temp = array_filter($parameters_temp, function ($el) { return $el !== ''; });

            foreach ($parameters_temp as $p) {
                $this->parameters[] = $p;
            }
        }

        if ($method === '*') {
            if (count($this->parameters) > 0) {
                $method = array_shift($this->parameters);
            } elseif ($default_suffix = $request->attributes->get('_default_suffix')) {
                $method = $default_suffix;
            } else {
                $method = 'index';
            }

        }
        if (method_exists($controller, 'before')) {
            $controller->before($request, $method);
        }

        if (method_exists($controller, 'router')) {
            list($controller, $method, $this->parameters) = $controller->router($request, $method, $this->parameters);
        } else {
            $method = 'action_'.$method;
        }

        if (!method_exists($controller, $method)) {
            throw new NotFoundHttpException;
            //throw new \InvalidArgumentException(sprintf('Method "%s::%s" does not exist.', get_class($controller), $method));
        }

        return array($controller, $method);
    }

    /**
     * Returns the arguments to pass to the controller.
     *
     * @param Request $request    A Request instance
     * @param mixed   $controller A PHP callable
     *
     * @return array
     *
     * @throws \RuntimeException When value for argument given is not provided
     *
     * @api
     */
    public function getArguments(\Symfony\Component\HttpFoundation\Request $request, $controller)
    {
        if ($this->parameters !== null) {
            return $this->parameters;
        }

        if (is_array($controller)) {
            $r = new \ReflectionMethod($controller[0], $controller[1]);
        } elseif (is_object($controller) && !$controller instanceof \Closure) {
            $r = new \ReflectionObject($controller);
            $r = $r->getMethod('__invoke');
        } else {
            $r = new \ReflectionFunction($controller);
        }

        return $this->doGetArguments($request, $controller, $r->getParameters());
    }

    protected function doGetArguments(\Symfony\Component\HttpFoundation\Request $request, $controller, array $parameters)
    {
        $attributes = $request->attributes->all();
        $arguments = array();
        foreach ($parameters as $param) {
            if (array_key_exists($param->name, $attributes)) {
                $arguments[] = $attributes[$param->name];
            } elseif ($param->getClass() && $param->getClass()->isInstance($request)) {
                $arguments[] = $request;
            } elseif ($param->isDefaultValueAvailable()) {
                $arguments[] = $param->getDefaultValue();
            } else {
                if (is_array($controller)) {
                    $repr = sprintf('%s::%s()', get_class($controller[0]), $controller[1]);
                } elseif (is_object($controller)) {
                    $repr = get_class($controller);
                } else {
                    $repr = $controller;
                }

                throw new \RuntimeException(sprintf('Controller "%s" requires that you provide a value for the "$%s" argument (because there is no default value or because there is a non optional argument after this one).', $repr, $param->name));
            }
        }

        return $arguments;
    }

    /**
     * Returns a callable for the given controller.
     *
     * @param string $controller A Controller string
     *
     * @return mixed A PHP callable
     *
     * @throws \InvalidArgumentException
     */
    protected function createController($controller)
    {
        if (false === strpos($controller, '::')) {
            throw new \InvalidArgumentException(sprintf('Unable to find controller "%s".', $controller));
        }

        list($class, $method) = explode('::', $controller, 2);

        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
        }

        return array(new $class(), $method);
    }
}
