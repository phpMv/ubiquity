<?php

namespace Ubiquity\controllers\semantic;

use Ajax\JsUtils;

trait InsertJqueryTrait {
	/**
	 *
	 * @var JsUtils
	 */
	public $jquery;

	public function insertJquerySemantic() {
		$this->jquery = new \Ajax\php\ubiquity\JsUtils ( [ "defer" => true ], $this );
		$this->jquery->semantic ( new \Ajax\Semantic () );
	}
}

