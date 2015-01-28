<?php

namespace Foolz\FoolFrame\Model;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Security extends Model
{
    /**
     * @var string
     */
    protected $token;
    /**
     * @var Config
     */
    protected $config;

    /**
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        $this->context = $context;
        $this->config = $context->getService('config');
        $this->token = uniqid('', true);
    }

    /**
     * Returns the CSRF token for this Security object
     *
     * @return string
     */
    public function getCsrfToken()
    {
        return $this->token;
    }

    public function getJsCsrfToken()
    {
        return '<script> var csrf_token = \''.addslashes($this->getCsrfToken()).'\'; </script>';
    }

    /**
     * Sets a CSRF cookie
     * @param Response $response
     */
    public function updateCsrfToken(Response $response)
    {
        $response->headers->setCookie(new Cookie(
            $this->getContext(),
            'csrf_token',
            $this->getCsrfToken(),
            86400 * 5
        ));
    }

    /**
     * Checks that the CSRF token cookie and POST match
     *
     * @param Request $request
     * @return bool
     */
    public function checkCsrfToken(Request $request)
    {
        $cookie_name = $this->config->get('foolz/foolframe', 'config', 'config.cookie_prefix').'csrf_token';

        return $request->cookies->get($cookie_name) !== null
            && $request->request->get('csrf_token') !== null
            && $request->cookies->get($cookie_name) === $request->request->get('csrf_token');
    }

    /**
     * Checks that the CSRF token cookie and POST match
     *
     * @param Request $request
     * @return bool
     */
    public function checkCsrfTokenGet(Request $request)
    {
        $cookie_name = $this->config->get('foolz/foolframe', 'config', 'config.cookie_prefix').'csrf_token';

        return $request->cookies->get($cookie_name) !== null
            && $request->query->get('csrf_token') !== null
            && $request->cookies->get($cookie_name) === $request->query->get('csrf_token');
    }
}
