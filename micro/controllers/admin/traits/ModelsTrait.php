<?php
namespace micro\controllers\admin\traits;

use Ajax\JsUtils;

/**
 * @author jc
 * @property JsUtils $jquery
 */
trait ModelsTrait{
	abstract public function _getAdminData();
	abstract public function _getAdminViewer();
	abstract public function _getAdminFiles();

}