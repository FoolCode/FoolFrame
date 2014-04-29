<?php

namespace Foolz\Foolframe\Model;

use Psr\Log\LoggerInterface;

class Logger extends Model implements LoggerInterface
{
    /**
     * @var LoggerInterface[]
     */
    protected $loggers = [];

    public function addLogger(LoggerInterface $logger)
    {
        $this->loggers[] = $logger;
    }

    public function emergency($message, array $context = array())
    {
        foreach ($this->loggers as $logger) {
            $logger->emergency($message, $context);
        }
    }

    public function alert($message, array $context = array())
    {
        foreach ($this->loggers as $logger) {
            $logger->alert($message, $context);
        }
    }

    public function critical($message, array $context = array())
    {
        foreach ($this->loggers as $logger) {
            $logger->critical($message, $context);
        }
    }

    public function error($message, array $context = array())
    {
        foreach ($this->loggers as $logger) {
            $logger->error($message, $context);
        }
    }

    public function warning($message, array $context = array())
    {
        foreach ($this->loggers as $logger) {
            $logger->warning($message, $context);
        }
    }

    public function notice($message, array $context = array())
    {
        foreach ($this->loggers as $logger) {
            $logger->notice($message, $context);
        }
    }

    public function info($message, array $context = array())
    {
        foreach ($this->loggers as $logger) {
            $logger->info($message, $context);
        }
    }

    public function debug($message, array $context = array())
    {
        foreach ($this->loggers as $logger) {
            $logger->debug($message, $context);
        }
    }

    public function log($level, $message, array $context = array())
    {
        foreach ($this->loggers as $logger) {
            $logger->log($message, $context);
        }
    }
}
