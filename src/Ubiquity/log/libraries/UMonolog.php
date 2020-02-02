<?php

namespace Ubiquity\log\libraries;

use Ubiquity\log\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\JsonFormatter;
use Ubiquity\log\LogMessage;
use Ubiquity\utils\base\UFileSystem;

/**
 * Logger class for Monolog
 * Ubiquity\log\libraries$UMonolog
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.3
 *
 */
class UMonolog extends Logger {
	/**
	 *
	 * @var \Monolog\Logger
	 */
	private $loggerInstance;
	private $handler;

	public function __construct($name, $level = \Monolog\Logger::INFO, $path = 'logs/app.log') {
		$this->loggerInstance = new \Monolog\Logger ( $name );
		$this->handler = new StreamHandler ( ROOT . DS . $path, $level );
		$this->handler->setFormatter ( new JsonFormatter () );
		$this->loggerInstance->pushHandler ( $this->handler );
	}

	private function createContext($context, $part, $extra = null) {
		return compact ( "context", "part", "extra" );
	}

	public function addProcessor($callback) {
		$this->loggerInstance->pushProcessor ( $callback );
	}

	public function _log($level, $context, $message, $part, $extra) {
		$this->loggerInstance->log ( $level, $message, $this->createContext ( $context, $part, $extra ) );
	}

	public function _info($context, $message, $part, $extra) {
		$this->loggerInstance->info ( $message, $this->createContext ( $context, $part, $extra ) );
	}

	public function _warn($context, $message, $part, $extra) {
		$this->loggerInstance->warning ( $message, $this->createContext ( $context, $part, $extra ) );
	}

	public function _error($context, $message, $part, $extra) {
		$this->loggerInstance->error ( $message, $this->createContext ( $context, $part, $extra ) );
	}

	public function _alert($context, $message, $part, $extra) {
		$this->loggerInstance->alert ( $message, $this->createContext ( $context, $part, $extra ) );
	}

	public function _critical($context, $message, $part, $extra) {
		$this->loggerInstance->critical ( $message, $this->createContext ( $context, $part, $extra ) );
	}

	public function _asObjects($reverse = true, $maxlines = 10, $contexts = null) {
		return UFileSystem::getLines ( $this->handler->getUrl (), $reverse, $maxlines, function (&$objects, $line) use ($contexts) {
			$jso = json_decode ( $line );
			if ($jso !== null) {
				if ($contexts === null || self::inContext ( $contexts, $jso->context->context )) {
					LogMessage::addMessage ( $objects, new LogMessage ( $jso->message, $jso->context->context, $jso->context->part, $jso->level, $jso->datetime, $jso->context->extra ) );
				}
			}
		} );
	}

	public function _clearAll() {
		$this->handler->close ();
		UFileSystem::deleteFile ( $this->handler->getUrl () );
	}

	public function _registerError() {
		// TODO register error handlers
	}
}

