<?php

namespace Ubiquity\utils\flash;

class FlashMessage {
	protected ?string $title;
	protected ?string $content;
	protected ?string $type;
	protected ?string $icon;

	public function __construct(string $content,string $title=NULL,string $type='info',string $icon=null){
		$this->setValues($content,$title,$type,$icon);
	}
	
	public function setValues(string $content,string $title=NULL,string $type=NULL,string $icon=null){
		$this->type = $type;
		$this->content=$content;
		$this->icon = $icon;
		$this->title = $title;
	}
	/**
	 * @return string|null
	 */
	public function getContent(): ?string {
		return $this->content;
	}

	/**
	 * @return string
	 */
	public function getType(): ?string {
		return $this->type;
	}

	/**
	 * @return string|null
	 */
	public function getIcon(): ?string {
		return $this->icon;
	}

	/**
	 * @param string $content
	 */
	public function setContent(string $content) {
		$this->content = $content;
	}

	/**
	 * @param string $type
	 */
	public function setType(string $type) {
		$this->type = $type;
	}
	
	public function addType(string $type){
		$this->type.=' '.$type;
	}

	/**
	 * @param string $icon
	 */
	public function setIcon(string $icon) {
		$this->icon = $icon;
	}
	/**
	 * @return string|null
	 */
	public function getTitle(): ?string {
		return $this->title;
	}

	/**
	 * @param string $title
	 */
	public function setTitle(string $title) {
		$this->title = $title;
	}
	
	public function parseContent(array $keyValues): self {
		$msg=$this->content;
		foreach ($keyValues as $key=>$value){
			$msg=\str_replace('{'.$key.'}', $value, $msg);
		}
		$this->content=$msg;
		return $this;
	}
}
