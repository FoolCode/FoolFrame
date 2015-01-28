<?php

namespace Foolz\FoolFrame\Model;

use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;
use Swift_SendmailTransport;

class Mailer extends Model
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Swift_Mailer
     */
    protected $mailer;

    public function __construct(Context $context)
    {
        parent::__construct($context);

        $this->config = $context->getService('config');

        $config = $this->config->get('foolz/foolframe', 'swiftmailer');
        switch ($config['transport']) {
            case 'smtp':
                $transport = Swift_SmtpTransport::newInstance()
                    ->setHost($config['host'])
                    ->setPort($config['port'])
                    ->setUsername($config['username'])
                    ->setPassword($config['password'])
                    ->setEncryption($config['encryption'])
                    ->setAuthMode('login');
                break;

            default:
                $transport = Swift_SendmailTransport::newInstance();
        }

        $this->mailer = Swift_Mailer::newInstance($transport);
    }

    public function create()
    {
        return Swift_Message::newInstance();
    }

    public function send($message)
    {
        return $this->mailer->send($message);
    }
}
