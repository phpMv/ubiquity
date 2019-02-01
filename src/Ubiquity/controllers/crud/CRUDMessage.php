<?php

namespace Ubiquity\controllers\crud;

class CRUDMessage {
	private $message;
	private $type;
	private $icon;
	private $title;
	private $timeout;
	private $_message;
	
	public function __construct($message,$title="",$type="",$icon="",$timeout=null){
		$this->message=$message;
		$this->title=$title;
		$this->type=$type;
		$this->icon=$icon;
		$this->timeout=$timeout;
		$this->_message=$message;
	}
	
	/**
	 * @return string
	 */
	public function getMessage() {
		return $this->_message;
	}

	/**
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @return mixed
	 */
	public function getIcon() {
		return $this->icon;
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @param string $message
	 */
	public function setMessage($message) {
		$this->_message = $message;
		$this->message=$message;
		return $this;
	}

	/**
	 * @param string $type
	 * @return $this
	 */
	public function setType($type) {
		$this->type = $type;
		return $this;
	}

	/**
	 * @param string $icon
	 * @return $this
	 */
	public function setIcon($icon) {
		$this->icon = $icon;
		return $this;
	}

	/**
	 * @param string $title
	 * @return $this
	 */
	public function setTitle($title) {
		$this->title = $title;
		return $this;
	}
	
	/**
	 * @return integer
	 */
	public function getTimeout() {
		return $this->timeout;
	}

	/**
	 * @param integer $timeout
	 * @return $this
	 */
	public function setTimeout($timeout) {
		$this->timeout = $timeout;
		return $this;
	}
	/**
	 * 
	 * @param string $value
	 * @return $this
	 */
	public function parse($value){
		$this->_message=str_replace("{value}", $value, $this->message);
		return $this;
	}
	
	public function parseContent($keyValues){
		$msg=$this->_message;
		foreach ($keyValues as $key=>$value){
			$msg=str_replace("{".$key."}", $value, $msg);
		}
		$this->_message=$msg;
		return $this;
	}

}

