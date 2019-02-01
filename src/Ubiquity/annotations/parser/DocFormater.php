<?php

namespace Ubiquity\annotations\parser;

class DocFormater {
	protected $types=["boolean","string","int","mixed","array"];
	protected $replacements=["types"=>"<span style='font-weight:bold;color: green;'>$1</span>","variables"=>"<span style='font-weight:bold;color: brown;'>$1</span>"];
	public function __construct(){

	}

	public function getReplacement($part){
		return $this->replacements[$part];
	}

	protected function getTypesRegex(){
		$items=\array_map(function($item){ return "(".$item.")";}, $this->types);
		return '^('.\implode('|', $items).').*?';
	}

	protected function getVariablesRegex(){
		return '(\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)';
	}

	protected function _replaceAll($search,$replacement,$subject){
		return \preg_replace('@'.$search.'@i', $replacement, $subject);
	}

	public function replaceTypes($str){
		return $this->_replaceAll($this->getTypesRegex(), $this->replacements["types"], $str);
	}

	public function replaceVariables($str){
		return $this->_replaceAll($this->getVariablesRegex(), $this->replacements["variables"], $str);
	}

	public function replaceAll($str){
		$str=$this->replaceTypes($str);
		return $this->replaceVariables($str);
	}
}
