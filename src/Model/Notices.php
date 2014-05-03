<?php

namespace Foolz\Foolframe\Model;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class Notices extends Model
{
    /**
     * @var array
     */
    public $notices = [];

    /**
     * @var array
     */
    public $flash_notices = [];

    /**
     * @var Request
     */
    protected $request;

    /**
     * @param Context $context
     * @param Request $request
     */
    public function __construct(Context $context, Request $request)
    {
        parent::__construct($context);

        $this->request = $request;
    }

    public function get()
    {
        return $this->notices;
    }

    /**
     * @param string $level
     * @param string $message
     */
    public function set($level, $message)
    {
        $this->notices[] = ['level' => $level, 'message' => $message];
    }

    /**
     * Get the flash notices
     *
     * @return array The flash notices, can be empty
     */
    public function getFlash()
    {
        if ($this->request->hasPreviousSession()) {
            return $this->request->getSession()->getFlashBag()->get('notice', []);
        }

        return [];
    }

    /**
     * Sets a flash notice
     *
     * @param  string  $level    The level of the message: success, warning, danger
     * @param  string  $message  The message
     */
    public function setFlash($level, $message)
    {
        if (!$this->request->hasSession()) {
            $this->request->setSession(new Session());
        }

        $this->flash_notices[] = ['level' => $level, 'message' => $message];
        $this->request->getSession()->getFlashBag()->set('notice', $this->flash_notices);
    }
}
