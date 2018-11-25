<?php
namespace Ubiquity\controllers\admin\traits;

use Ubiquity\controllers\Startup;
use Ubiquity\cache\CacheManager;
use Ubiquity\orm\OrmUtils;
use Ubiquity\cache\ClassUtils;
use Ubiquity\orm\reverse\DatabaseReversor;
use Ubiquity\db\reverse\DbGenerator;
use Ubiquity\utils\http\URequest;
use Ubiquity\orm\DAO;
use Ubiquity\db\export\DbExport;
use Ajax\semantic\html\collections\HtmlMessage;

/**
 * @author jc
 * @property \Ajax\JsUtils $jquery
 */
trait DatabaseTrait{
	abstract public function _getAdminData();
	abstract public function _getAdminViewer();
	abstract public function _getFiles();
	abstract public function loadView($viewName, $pData=NULL, $asString=false);
	abstract protected function showSimpleMessage($content, $type, $title=null,$icon="info", $timeout=NULL, $staticName=null):HtmlMessage;

	protected function getModels(){
		$config=Startup::getConfig();
		$models=CacheManager::getModels($config,true);
		$result=[];
		foreach ($models as $model){
			$table=OrmUtils::getTableName($model);
			$simpleM=ClassUtils::getClassSimpleName($model);
			if($simpleM!==$table)
				$result[$table]=$simpleM."[".$table."]";
			else
				$result[$table]=$simpleM;
		}
		return $result;
	}

	public function createSQLScript(){
		if(URequest::isPost()){
			$db=$_POST["dbName"];
			if(DAO::isConnected()){
				$actualDb=DAO::$db->getDbName();
			}
			$generator=new DatabaseReversor(new DbGenerator());
			$generator->createDatabase($db);
			$frm=$this->jquery->semantic()->htmlForm("form-sql");
			$text=$frm->addElement("sql", $generator->__toString(),"SQL script","div","ui segment editor");
			$text->getField()->setProperty("style", "background-color: #002B36;");
			$bts=$this->jquery->semantic()->htmlButtonGroups("buttons");
			$bts->addElement("Generate database")->addClass("green");
			if(isset($actualDb) && $actualDb!==$db){
				$btExport=$bts->addElement("Export datas script : ".$actualDb." => ".$db);
				$btExport->addIcon("exchange");
				$btExport->postOnClick($this->_getFiles()->getAdminBaseRoute()."/exportDatasScript","{}","#div-datas");
			}
			$frm->addDivider();
			$this->jquery->exec("setAceEditor('sql');",true);
			$this->jquery->compile($this->view);
			$this->loadView($this->_getFiles()->getViewDatabaseCreate());
		}
	}

	public function exportDatasScript(){
		$dbExport=new DbExport();
		$frm=$this->jquery->semantic()->htmlForm("form-sql-datas");
		$text=$frm->addElement("datas-sql", $dbExport->exports(),"Datas export script","div","ui segment editor");
		$text->getField()->setProperty("style", "background-color: #002B36;");
		$this->jquery->exec("setAceEditor('datas-sql');",true);
		$this->jquery->compile($this->view);
		$this->loadView($this->_getFiles()->getViewDatasExport());
	}
}
