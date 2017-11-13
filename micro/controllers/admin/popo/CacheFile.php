<?php

namespace micro\controllers\admin\popo;

use micro\utils\FsUtils;

class CacheFile {
	private $type;
	private $name;
	private $timestamp;
	private $size;
	private $file;

	public function __construct($type="",$name="",$timestamp=0,$size=0,$file=""){
		$this->type=$type;
		$this->name=$name;
		$this->timestamp=$timestamp;
		$this->size=$size;
		$this->file=$file;
	}

	public static function init($folder,$type){
		$files=FsUtils::glob_recursive($folder . DS . '*');
		$result=[];
		foreach ($files as $file){
			if (is_file($file)) {
				$result[]=new CacheFile($type,\basename($file),\filectime($file),\filesize($file),$file);
			}
		}
		if(\sizeof($result)==0)
			$result[]=new CacheFile($type,"","","","");
		return $result;
	}

	public static function delete($folder){
		$files=FsUtils::glob_recursive($folder . DS . '*');
		foreach ($files as $file){
			if (is_file($file)) {
				\unlink($file);
			}
		}
	}

	public function getName() {
		return $this->name;
	}

	public function setName($name) {
		$this->name=$name;
		return $this;
	}

	public function getTimestamp() {
		return $this->timestamp;
	}

	public function setTimestamp($timestamp) {
		$this->timestamp=$timestamp;
		return $this;
	}

	public function getSize() {
		return $this->size;
	}

	public function setSize($size) {
		$this->size=$size;
		return $this;
	}

	public function getType() {
		return $this->type;
	}

	public function setType($type) {
		$this->type=$type;
		return $this;
	}

	public function getFile() {
		return $this->file;
	}

	public function setFile($file) {
		$this->file=$file;
		return $this;
	}


}
