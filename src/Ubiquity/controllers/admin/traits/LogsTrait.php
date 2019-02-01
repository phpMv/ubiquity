<?php

namespace Ubiquity\controllers\admin\traits;

use Ajax\semantic\html\collections\HtmlMessage;
use Ubiquity\utils\http\URequest;
use Ubiquity\log\Logger;
use Ubiquity\utils\base\UArray;
use Ubiquity\controllers\Startup;


/**
 *
 * @author jc
 * @property \Ajax\php\ubiquity\JsUtils $jquery
 * @property \Ubiquity\views\View $view
 */
trait LogsTrait{

	abstract public function _getAdminData();

	abstract public function _getAdminViewer();

	abstract public function _getFiles();

	abstract public function loadView($viewName, $pData = NULL, $asString = false);

	abstract public function git();

	abstract protected function showConfMessage($content, $type, $title,$icon,$url, $responseElement, $data, $attributes = NULL): HtmlMessage;

	abstract protected function showSimpleMessage($content, $type, $title=null,$icon = "info", $timeout = NULL, $staticName = null): HtmlMessage;

	public function logsRefresh() {
		$maxLines=URequest::post("maxLines",null);
		if(!is_numeric($maxLines)){
			$maxLines=null;
		}
		$groupBy=null;
		if(isset($_POST["group-by"])){
			$values=array_diff(explode(",", $_POST["group-by"]),[""]);
			if(sizeof($values)>0){
				$groupBy=$values;
			}
		}
		$contexts=null;
		if(isset($_POST["contexts"])){
			$values=array_diff(explode(",", $_POST["contexts"]),[""]);
			if(sizeof($values)>0){
				$contexts=$values;
			}
		}
		$dt=$this->_getAdminViewer()->getLogsDataTable($maxLines,!isset($_POST["ck-reverse"]),$groupBy,$contexts);
		echo $dt;
		echo $this->jquery->compile ( $this->view );
	}
	
	public function deleteAllLogs(){
		Logger::clearAll();
		$this->logsRefresh();
	}
	
	public function activateLog(){
		$this->startStopLogging();
	}
	
	public function deActivateLog(){
		$this->startStopLogging(false);
	}
	
	private function startStopLogging($start=true){
		$config=Startup::getConfig();
		$config["debug"]=$start;
		$content="<?php\nreturn ".UArray::asPhpArray($config,"array",1,true).";";
		Startup::saveConfig($content);
		Startup::reloadConfig();
		Logger::init($config);
		$this->logs();
	}

}
