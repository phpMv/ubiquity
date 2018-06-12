<?php

namespace Ubiquity\log;

class LogMessage {
	
	private $message;
	private $context;
	private $part;
	private $level;
	private $extra;
	private $datetime;
	private $count=1;
	
	public function __construct($message="",$context="",$part="",$level=0,$datetime=null,$extra=null){
		$this->message=$message;
		$this->context=$context;
		$this->part=$part;
		$this->level=$level;
		$this->datetime=$datetime;
		$this->extra=$extra;
	}
	/**
	 * @return mixed
	 */
	public function getMessage() {
		return $this->message;
	}

	/**
	 * @return mixed
	 */
	public function getContext() {
		return $this->context;
	}

	/**
	 * @return mixed
	 */
	public function getPart() {
		return $this->part;
	}

	/**
	 * @return mixed
	 */
	public function getLevel() {
		return $this->level;
	}

	/**
	 * @return mixed
	 */
	public function getExtra() {
		return $this->extra;
	}

	/**
	 * @return mixed
	 */
	public function getDatetime() {
		return $this->datetime;
	}

	/**
	 * @param mixed $message
	 */
	public function setMessage($message) {
		$this->message = $message;
	}

	/**
	 * @param mixed $context
	 */
	public function setContext($context) {
		$this->context = $context;
	}

	/**
	 * @param mixed $part
	 */
	public function setPart($part) {
		$this->part = $part;
	}

	/**
	 * @param mixed $level
	 */
	public function setLevel($level) {
		$this->level = $level;
	}

	/**
	 * @param mixed $extra
	 */
	public function setExtra($extra) {
		$this->extra = $extra;
	}

	/**
	 * @param mixed $datetime
	 */
	public function setDatetime($datetime) {
		$this->datetime = $datetime;
	}
	
	public function incCount(){
		$this->count++;
	}
	/**
	 * @return mixed
	 */
	public function getCount() {
		return $this->count;
	}
	
	public function equals(LogMessage $message){
		return $this->message===$message->getMessage() && $this->context===$message->getContext() && $this->part===$message->getPart();
	}

	public static function addMessage(&$messages,LogMessage $newMessage){
		if(sizeof($messages)>0){
			$lastM=end($messages);
			if($newMessage->equals($lastM)){
				return $lastM->incCount();
			}
		}
		$messages[]=$newMessage;
	}
	
}

