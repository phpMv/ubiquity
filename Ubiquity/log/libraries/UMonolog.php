<?php

namespace Ubiquity\log\libraries;

use Ubiquity\log\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\JsonFormatter;
use Ubiquity\log\LogMessage;

class UMonolog extends Logger{
	
	private $loggerInstance;
	private $handler;
	public function __construct($name,$path='logs/app.log',$level=\Monolog\Logger::INFO){
		$this->loggerInstance = new \Monolog\Logger($name);
		$this->handler=new StreamHandler(ROOT.DS.$path,$level);
		$this->handler->setFormatter(new JsonFormatter());
		$this->loggerInstance->pushHandler($this->handler);
	}

	public function _log($level,$context, $message,$part) {
		return $this->loggerInstance->log($level,$message,[$context,$part]);
	}
	
	public function _info($context, $message, $part) {
		return $this->loggerInstance->info($message,[$context,$part]);
	}
	
	public function _warn($context, $message,$part) {
		return $this->loggerInstance->warn($message,[$context,$part]);
	}

	public function _error($context, $message,$part) {
		return $this->loggerInstance->error($message,[$context,$part]);
	}
	public function _alert($context, $message, $part) {
		return $this->loggerInstance->alert($message,[$context,$part]);
	}

	public function _critical($context, $message, $part) {
		return $this->loggerInstance->critical($message,[$context,$part]);
	}
	
	public function _asObjects($reverse=true){
		$objects=[];
		if(file_exists($file=$this->handler->getUrl())){
			$handle = fopen($file, "r");
			if ($handle) {
				while (($line = fgets($handle)) !== false) {
					$jso=json_decode($line);
					LogMessage::addMessage($objects, new LogMessage($jso->message,$jso->context[0],$jso->context[1],$jso->level,$jso->datetime->date));
				}
				fclose($handle);
			} else {
				// error opening the file.
			}
		}
		if($reverse){
			return array_reverse($objects);
		}
		return $objects;
	}


}

