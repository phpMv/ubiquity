<?php

namespace Ubiquity\utils\base;
/**
 * 
 * Ubiquity\utils\base$UConfigFile
 * This class is part of Ubiquity
 * @author jc
 * @version 1.0.0
 * @since 2.4.11
 *
 */
class UConfigFile {
	
	private string $filename;
	
	private array $data=[];
	
	public function __construct(string $name){
		$this->filename=\ROOT . "config/$name.config.php";
	}
	
	public function load(array $default=[]): array {
		return $this->data=self::load_($this->filename,$default);
	}
	
	public function save(): bool {
		return self::save_($this->filename, $this->data);
	}
	
	public function get(string $key,$default=null){
		return $this->data[$key]??$default;
	}
	
	public function set(string $key,$value):self{
		$this->data[$key]=$value;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getData(): array {
		return $this->data;
	}

	/**
	 * @param array $data
	 */
	public function setData(array $data): self {
		$this->data = $data;
		return $this;
	}


	
	public static function load_(string $filename,array $default=[]):array {
		if(\file_exists($filename)){
			return include($filename);
		}
		return $default;
	}
	
	public static function save_(string $filename,array $data):bool{
		return false!==UFileSystem::save($filename, '<?php return '.UArray::asPhpArray_($data,1,true).';');
	}
}

