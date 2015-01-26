<?php

namespace Foolz\FoolFrame\Controller;

use Foolz\FoolFrame\Model\Auth;
use Foolz\FoolFrame\Model\Context;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Common implements ControllerInterface
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Request
     */
    protected $request;

    public function setContext(Context $context)
    {
        $this->context = $context;
    }

    public function getContext()
    {
        return $this->context;
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return Auth
     */
    public function getAuth()
    {
        return $this->getContext()->getService('auth');
    }

    public function getPost($key = false, $fallback = false)
    {
        if (!$key) {
            return $this->getRequest()->request->all();
        }

        return $this->getRequest()->request->get($key, $fallback);
    }

    public function checkCsrfToken()
    {
        return $this->getContext()->getService('security')->checkCsrfToken($this->getRequest());
    }

    public function checkCsrfTokenGet()
    {
        return $this->getContext()->getService('security')->checkCsrfTokenGet($this->getRequest());
    }

    public function getQuery($key = false, $fallback = false)
    {
        if (!$key) {
            return $this->getRequest()->query->all();
        }

        return $this->getRequest()->query->get($key, $fallback);
    }

    public function getCookie($key = false, $fallback = false)
    {
        if (!$key) {
            return $this->getRequest()->cookies->all();
        }

        $config = $this->getContext()->getService('config');
        return $this->getRequest()
            ->cookies->get($config->get('foolz/foolframe', 'config', 'config.cookie_prefix').$key, $fallback);
    }
}
