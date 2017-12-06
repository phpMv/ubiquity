<?php
namespace micro\controllers\admin\traits;

use Ajax\JsUtils;
use Ajax\semantic\html\elements\HtmlLabel;

/**
 * @author jc
 * @property JsUtils $jquery
 */
trait ControllersTrait{
	abstract public function _getAdminData();
	abstract public function _getAdminViewer();
	abstract public function _getAdminFiles();
	public function _createView(){
		$viewname=$_POST["view"];
		//if(\file_exists(ROOT . DS . "views/".$viewname)){
			$lbl=new HtmlLabel("",$viewname,"browser");
			$lbl->addClass("violet");
			echo $lbl;
		//}
	}
}
