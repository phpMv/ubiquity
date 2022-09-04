<?php

namespace Ubiquity\controllers;

/**
 * Default controller displaying php views only.
 * Ubiquity\controllers$ControllerView
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.1
 *
 */
abstract class SimpleViewController extends Controller {

	public function __construct() {
	}

	protected function _includeFileAsString($filename, $pData) {
		if (isset ( $pData )) {
			\extract ( $pData );
		}
		\ob_start ();
		include ($filename);
		return \ob_get_clean ();
	}

	/**
	 * Loads the php view $viewName possibly passing the variables $pdata
	 *
	 * @param string $viewName The name of the view to load
	 * @param mixed $pData Variable or associative array to pass to the view
	 *        If a variable is passed, it will have the name **$data** in the view,
	 *        If an associative array is passed, the view retrieves variables from the table's key names
	 * @param boolean $asString If true, the view is not displayed but returned as a string (usable in a variable)
	 * @throws \Exception
	 * @return ?string null or the view content if **$asString** parameter is true
	 */
	public function loadView(string $viewName, $pData = NULL, bool $asString = false) {
		$filename = \ROOT . \DS . 'views' . \DS . $viewName;
		if ($asString) {
			return $this->_includeFileAsString ( $filename, $pData );
		}
		if (isset ( $pData )) {
			\extract ( $pData );
		}
		include ($filename);
	}
}
