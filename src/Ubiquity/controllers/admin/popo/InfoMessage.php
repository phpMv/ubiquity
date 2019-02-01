<?php

namespace Ubiquity\controllers\admin\popo;

class InfoMessage {
	private $type;
	private $content;

	public function __construct($type,$content){
		$this->type=$type;
		$this->content=$content;
	}

	public function getType() {
		return $this->type;
	}

	public function setType($type) {
		$this->type=$type;
		return $this;
	}

	public function getContent() {
		return $this->content;
	}

	public function setContent($content) {
		$this->content=$content;
		return $this;
	}
}
