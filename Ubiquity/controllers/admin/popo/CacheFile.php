<?php

namespace Ubiquity\controllers\admin\popo;

use Ubiquity\utils\base\UFileSystem;

class CacheFile {
	private $type;
	private $name;
	private $timestamp;
	private $size;
	private $file;

	public function __construct($type="", $name="", $timestamp=0, $size=0, $fileKey="") {
		$this->type=$type;
		$this->name=$name;
		$this->timestamp=$timestamp;
		$this->size=$size;
		$this->file=$fileKey;
	}

	public static function initFromFiles($folder, $type, $keyFunction=null) {
		$files=UFileSystem::glob_recursive($folder . \DS . '*');
		$result=[ ];
		if (!isset($keyFunction)) {
			$keyFunction=function ($file) {
				return \basename($file);
			};
		}
		foreach ( $files as $file ) {
			if (is_file($file)) {
				$result[]=new CacheFile($type, $keyFunction($file), \filectime($file), \filesize($file), $file);
			}
		}
		if (\sizeof($result) == 0)
			$result[]=new CacheFile($type, "", "", "", "");
		return $result;
	}

	public static function delete($folder) {
		$files=UFileSystem::glob_recursive($folder . \DS . '*');
		foreach ( $files as $file ) {
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
