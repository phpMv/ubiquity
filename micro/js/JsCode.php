<?php

namespace micro\js;
/**
 * Code javascript
 * @author jc
 * @version 1.0.0.1
 * @package js
 */
class JsCode {
	private $code;
	/**
	 * Ajoute les balises de Script avant et après le code si nécessaire
	 */
	private static function addScript($code){
		return preg_filter("/(\<script[^>]*?\>)?(.*)(\<\/script\>)?/si", "<script>$2 </script>\n", $code,1);
	}

	public function __construct($code){
		$this->code=$code;
	}

	public function __toString(){
		return $this->addScript($this->code);
	}

	public function getCode() {
		return $this->code;
	}

	public function setCode($code) {
		$this->code=$code;
		return $this;
	}

}