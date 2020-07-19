<?php

namespace Ubiquity\controllers;

/**
 * Default controller displaying php views only with an async server (Swoole, workerman).
 * Ubiquity\controllers$ControllerView
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.1
 *
 */
abstract class SimpleViewAsyncController extends SimpleViewController {
	protected static $views = [ ];

	protected function getViewKey($filename, $pdata) {
		if (isset ( $pdata )) {
			return \md5 ( $filename . \json_encode ( $pdata ) );
		}
		return \md5 ( $filename );
	}

	protected function _includeFileAsString($filename, $pdata) {
		$key = $this->getViewKey ( $filename, $pdata );
		if (! isset ( self::$views [$key] )) {
			if (isset ( $pdata )) {
				\extract ( $pdata );
			}
			\ob_start ();
			include ($filename);
			return self::$views [$key] = \ob_get_clean ();
		}
		return self::$views [$key];
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
	 * @return string null or the view content if **$asString** parameter is true
	 */
	public function loadView($viewName, $pData = NULL, $asString = false) {
		$filename = \ROOT . \DS . 'views' . \DS . $viewName;
		if ($asString) {
			return $this->_includeFileAsString ( $filename, $pData );
		}
		echo $this->_includeFileAsString ( $filename, $pData );
	}
}
