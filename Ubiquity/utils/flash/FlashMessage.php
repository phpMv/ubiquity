<?php

namespace Ubiquity\utils\flash;

class FlashMessage {
	protected $title;
	protected $content;
	protected $type;
	protected $icon;

	public function __construct($content,$title=NULL,$type="info",$icon=null){
		$this->setValues($content,$title,$type,$icon);
	}
	
	public function setValues($content,$title=NULL,$type=NULL,$icon=null){
		if(isset($type))
			$this->type=$type;
		$this->content=$content;
		if(isset($icon))
			$this->icon=$icon;
		if(isset($title))
			$this->title=$title;
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
	
	public function addType($type){
		$this->type.=" ".$type;
	}

	/**
	 * @param mixed $icon
	 */
	public function setIcon($icon) {
		$this->icon = $icon;
	}
	/**
	 * @return mixed
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @param mixed $title
	 */
	public function setTitle($title) {
		$this->title = $title;
	}
	
	public function parseContent($keyValues){
		$msg=$this->content;
		foreach ($keyValues as $key=>$value){
			$msg=str_replace("{".$key."}", $value, $msg);
		}
		$this->content=$msg;
		return $this;
	}


}

