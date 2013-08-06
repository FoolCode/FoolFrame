<?php

namespace Foolz\Foolframe\View;

use Foolz\Foolframe\Model\Config;
use Foolz\Foolframe\Model\Context;
use Foolz\Foolframe\Model\Logger;
use Foolz\Foolframe\Model\Notices;
use Foolz\Foolframe\Model\Preferences;
use Foolz\Foolframe\Model\Uri;
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