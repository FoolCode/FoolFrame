<?php
namespace Foolz\Foolframe\Model;

use Psr\Log\LoggerInterface;

class ExceptionHandler extends \Symfony\Component\Debug\ExceptionHandler {
	/**
	 * @var LoggerInterface
	 */
	protected $logger = null;

	/**
	 * @var LoggerInterface
	 */
	protected $logger_trace = null;

	/**
	 * Classic logger
	 *
	 * @param LoggerInterface $logger
	 */
	public function setLogger(LoggerInterface $logger) {
		$this->logger = $logger;
	}

	/**
	 * Logger that will print also stack trace
	 *
	 * @param LoggerInterface $logger
	 */
	public function setLoggerTrace(LoggerInterface $logger) {
		$this->logger_trace = $logger;
	}

	/**
	 * Sends a response for the given Exception.
	 *
	 * If you have the Symfony HttpFoundation component installed,
	 * this method will use it to create and send the response. If not,
	 * it will fallback to plain PHP functions.
	 *
	 * @param \Exception $exception An \Exception instance
	 *
	 * @see sendPhpResponse
	 * @see createResponse
	 */
	public function handle(\Exception $exception) {
		if ($this->logger !== null) {
			$this->logger->error($exception->getMessage());
		}

		if ($this->logger_trace !== null) {
			$string = $exception->getMessage()."\r\n";
			foreach ($exception->getTrace() as $trace) {
				if (isset($trace['file'])) $string .= 'at '.$trace['file'].'('.$trace['line'].') ';
				if (isset($trace['class'])) $string .= 'in '. $trace['class'].$trace['type'];
				if (isset($trace['function'])) $string .= $trace['function'].'('.$this->stringify($trace['args']).')';
				$string .= "\r\n";
			}
			echo $string;
			$this->logger_trace->error($string);
		}

		if (class_exists('Symfony\Component\HttpFoundation\Response')) {
			$this->createResponse($exception)->send();
		} else {
			$this->sendPhpResponse($exception);
		}
	}

	/**
	 * Creates an acceptable representation of $trace['args']
	 *
	 * @param     $array
	 * @param int $depth
	 *
	 * @return string
	 */
	public function stringify($array, $depth = 0) {
		if ($depth > 2) {
			return '[...]';
		}

		$result = [];
		foreach ($array as $a) {
			if (is_array($a)) {
				$result[] = '['.$this->stringify($a, $depth + 1).']';
				continue;
			}

			if (is_object($a)) {
				$result[] = get_class($a);
				continue;
			}

			$result[] = $a;
		}

		var_dump($result);
		return implode(', ', $result);
	}
}