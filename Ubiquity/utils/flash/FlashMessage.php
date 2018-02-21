<?php

namespace Ubiquity\utils\flash;

class FlashMessage {
	protected $content;
	protected $type;
	protected $icon;

	public function __construct($type,$content,$icon=null){
		$this->type=$type;
		$this->content=$content;
		$this->icon=$icon;
	}
	/**
	 * @return mixed
	 */
	public function getContent() {
		return $this->content;
	}

	/**
	 * @return mixed
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
	 * @param mixed $content
	 */
	public function setContent($content) {
		$this->content = $content;
	}

	/**
	 * @param mixed $type
	 */
	public function setType($type) {
		$this->type = $type;
	}

	/**
	 * @param mixed $icon
	 */
	public function setIcon($icon) {
		$this->icon = $icon;
	}

}

