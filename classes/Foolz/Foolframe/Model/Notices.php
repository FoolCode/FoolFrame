<?php

namespace Foolz\Foolframe\Model;

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
     * @var \Symfony\Component\HttpFoundation\Session\Session
     */
    protected $session;

    /**
     * @param Context $context
     * @param Session $session
     */
    public function __construct(Context $context, Session $session)
    {
        parent::__construct($context);

        $this->session = $session;
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
        return $this->session->getFlashBag()->get('notice', []);
    }

    /**
     * Sets a flash notice
     *
     * @param  string  $level    The level of the message: success, warning, danger
     * @param  string  $message  The message
     */
    public function setFlash($level, $message)
    {
        $this->flash_notices[] = ['level' => $level, 'message' => $message];
        $this->session->getFlashBag()->set('notice', $this->flash_notices);
    }
}