<?php

namespace Foolz\FoolFrame\View;

use Foolz\FoolFrame\Model\Auth;
use Foolz\FoolFrame\Model\Config;
use Foolz\FoolFrame\Model\Context;
use Foolz\FoolFrame\Model\Form;
use Foolz\FoolFrame\Model\Logger;
use Foolz\FoolFrame\Model\Notices;
use Foolz\FoolFrame\Model\Preferences;
use Foolz\FoolFrame\Model\Security;
use Foolz\FoolFrame\Model\Uri;
use Foolz\Profiler\Profiler;
use Symfony\Component\HttpFoundation\Request;

class View extends \Foolz\Theme\View
{
    /**
     * @return Context
     */
    public function getContext()
    {
        return $this->getBuilderParamManager()->getParam('context');
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->getBuilderParamManager()->getParam('request');
    }

    /**
     * @return Preferences
     */
    public function getPreferences()
    {
        return $this->getBuilderParamManager()->getParam('context')->getService('preferences');
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->getBuilderParamManager()->getParam('context')->getService('config');
    }

    /**
     * @return Uri
     */
    public function getUri()
    {
        return $this->getBuilderParamManager()->getParam('context')->getService('uri');
    }

    public function getForm()
    {
        return new Form($this->getRequest());
    }

    /**
     * @return Auth
     */
    public function getAuth()
    {
        return $this->getBuilderParamManager()->getParam('context')->getService('auth');
    }

    /**
     * @return Security
     */
    public function getSecurity()
    {
        return $this->getBuilderParamManager()->getParam('context')->getService('security');
    }

    /**
     * @return Logger
     */
    public function getLogger()
    {
        return $this->getBuilderParamManager()->getParam('context')->getService('logger');
    }

    /**
     * @return Profiler
     */
    public function getProfiler()
    {
        return $this->getBuilderParamManager()->getParam('context')->getService('profiler');
    }

    /**
     * @return Notices
     */
    public function getNotices()
    {
        return $this->getBuilderParamManager()->getParam('context')->getService('notices');
    }

    public function getPost($key = false, $fallback = false)
    {
        if (!$key) {
            return $this->getRequest()->request->all();
        }

        return $this->getRequest()->request->get($key, $fallback);
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

        return $this->getRequest()
            ->cookies->get($this->getConfig()->get('foolz/foolframe', 'config', 'config.cookie_prefix').$key, $fallback);
    }
}
