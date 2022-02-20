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
	public function getMessage(): string {
		return $this->_message;
	}

	/**
	 * @return string
	 */
	public function getType(): string {
		return $this->type;
	}

	/**
	 * @return mixed
	 */
	public function getIcon(): string {
		return $this->icon;
	}

	/**
	 * @return string
	 */
	public function getTitle(): string {
		return $this->title;
	}

	/**
	 * @param string $message
	 * @return CRUDMessage
	 */
	public function setMessage(string $message): CRUDMessage {
		$this->_message = $message;
		$this->message=$message;
		return $this;
	}

	/**
	 * @param string $type
	 * @return $this
	 */
	public function setType(string $type): CRUDMessage {
		$this->type = $type;
		return $this;
	}

	/**
	 * @param string $icon
	 * @return $this
	 */
	public function setIcon(string $icon): CRUDMessage {
		$this->icon = $icon;
		return $this;
	}

	/**
	 * @param string $title
	 * @return $this
	 */
	public function setTitle(string $title): CRUDMessage {
		$this->title = $title;
		return $this;
	}
	
	/**
	 * @return integer
	 */
	public function getTimeout(): ?int {
		return $this->timeout;
	}

	/**
	 * @param integer $timeout
	 * @return $this
	 */
	public function setTimeout(int $timeout): CRUDMessage {
		$this->timeout = $timeout;
		return $this;
	}

	/**
	 *
	 * @param string $value
	 * @return $this
	 */
	public function parse(string $value): CRUDMessage {
		$this->_message=\str_replace("{value}", $value, $this->message);
		return $this;
	}

	/**
	 * @param array $keyValues
	 * @return $this
	 */
	public function parseContent(array $keyValues): CRUDMessage {
		$msg=$this->_message;
		foreach ($keyValues as $key=>$value){
			$msg=str_replace("{".$key."}", $value, $msg);
		}
		$this->_message=$msg;
		return $this;
	}

}

