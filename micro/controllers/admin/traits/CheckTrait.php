<?php
namespace micro\controllers\admin\traits;

use micro\controllers\Startup;
use micro\orm\DAO;
use micro\utils\StrUtils;
use micro\cache\CacheManager;
use micro\cache\ClassUtils;
use micro\controllers\admin\popo\InfoMessage;
use micro\db\Database;
use Ajax\semantic\html\base\HtmlSemDoubleElement;
use Ajax\JsUtils;
use Ajax\semantic\html\elements\HtmlIcon;
use micro\utils\FsUtils;

/**
 * @author jc
 * @property array $steps
 * @property int $activeStep
 * @property string $engineering
 * @property JsUtils $jquery
 */
trait CheckTrait{
	private $messages=["error"=>[],"info"=>[]];
	abstract protected function getModelSteps();
	abstract protected function getActiveModelStep();
	abstract protected function getNextModelStep();
	abstract protected function displayModelsMessages($type,$messagesToDisplay);
	abstract public function _isModelsCompleted();
	/**
	 * @return UbiquityMyAdminFiles
	 */
	abstract public function _getAdminFiles();

	protected function _checkStep($niveau=null){
		$nbChecked=1;
		$niveauMax=$niveau;
		if(!isset($niveau))
			$niveauMax=$this->activeStep-1;
			$steps=$this->getModelSteps();
			while ($nbChecked<=$niveauMax && $this->hasNoError()){
				$this->_modelCheckOneNiveau($steps[$nbChecked][1], $nbChecked);
				$nbChecked++;
			}
			if($this->hasError() && !isset($niveau)){
				$this->activeStep=$nbChecked-1;
				$this->steps[$this->engineering][$this->activeStep][0]="warning sign red";
			}
	}

	protected function _modelCheckOneNiveau($name,$stepNum){
		$config=Startup::getConfig();
		switch ($name){
			case "Conf":
				if($this->missingKeyInConfigMessage("Database is not well configured in <b>app/config/config.php</b>", Startup::checkDbConfig())===false){
					$this->_addInfoMessage("settings", "Database is well configured");
				}
				break;
			case "Connexion": case "Database":
				$this->checkDatabase($config,"database");
				break;
			case "Models":
				$this->checkModels($config);
				break;
			case "Cache":
				$this->checkModelsCache($config);
				break;
		}
	}

	protected function checkDatabase($config,$infoIcon="database"){
		$db=$config["database"];
		if(!DAO::isConnected()){
			$this->_addErrorMessage("warning", "connection to the database is not established (probably in <b>app/config/services.php</b> file).");
			try{
				if($db["dbName"]!==""){
					$this->_addInfoMessage($infoIcon, "Attempt to connect to the database <b>".$db["dbName"]."</b> ...");
					$db=new Database($db["type"],$db["dbName"],@$db["serverName"],@$db["port"],@$db["user"],@$db["password"],@$db["cache"]);
					$db->connect();
				}
			}catch(\Exception $e){
				$this->_addErrorMessage("warning", $e->getMessage());
			}
		}else{
			$this->_addInfoMessage($infoIcon, "The connection to the database <b>".$db["dbName"]."</b> is established.");
		}
	}

	protected function checkModels($config,$infoIcon="sticky note"){
		if($this->missingKeyInConfigMessage("Models namespace is not well configured in <b>app/config/config.php</b>", Startup::checkModelsConfig())===false){
			$modelsNS=@$config["mvcNS"]["models"];
			$this->_addInfoMessage($infoIcon, "Models namespace <b>".$modelsNS."</b> is ok.");
			$dir=FsUtils::cleanPathname(ROOT.DS.$modelsNS);
			if(\file_exists($dir)===false){
				$this->_addErrorMessage("warning", "The directory <b>".$dir."</b> does not exists.");
			}else{
				$this->_addInfoMessage($infoIcon, "The directory for models namespace <b>".$dir."</b> exists.");
				$files=CacheManager::getModelsFiles($config,true);
				if(\sizeof($files)===0){
					$this->_addErrorMessage("warning", "No file found in <b>".$dir."</b> folder.");
				}else{
					foreach ($files as $file){
						$completeName=ClassUtils::getClassFullNameFromFile($file);
						$parts=\explode("\\", $completeName);
						$classname=\array_pop($parts);
						$ns=\implode("\\", $parts);
						if($ns!==$modelsNS){
							$this->_addErrorMessage("warning", "The namespace <b>".$ns."</b> would be <b>".$modelsNS."</b> for the class <b>".$classname."</b>.");
						}else{
							$this->_addInfoMessage($infoIcon, "The namespace for the class <b>".$classname."</b> is ok.");
						}
					}
				}
			}
		}
	}

	protected function checkModelsCache($config,$infoIcon="lightning"){
		if(!isset($config["cacheDirectory"]) || StrUtils::isNull($config["cacheDirectory"])){
			self::missingKeyInConfigMessage("Cache directory is not well configured in <b>app/config/config.php</b>", ["cacheDirectory"]);
		}else{
			$cacheDir=FsUtils::cleanPathname(ROOT.DS.$config["cacheDirectory"]);
			$this->_addInfoMessage($infoIcon, "Models cache directory is well configured in config file.");
			$cacheDirs=CacheManager::getCacheDirectories($config,true);
			if(\file_exists($cacheDir)===false){
				$this->_addErrorMessage("warning", "The cache directory <b>".$cacheDir."</b> does not exists.");
			}else{
				$modelsCacheDir=FsUtils::cleanPathname($cacheDirs["models"]);
				$this->_addInfoMessage($infoIcon, "Cache directory <b>".$cacheDir."</b> exists.");
				if(\file_exists($modelsCacheDir)===false){
					$this->_addErrorMessage("warning", "The models cache directory <b>".$modelsCacheDir."</b> does not exists.");
				}else{
					$this->_addInfoMessage($infoIcon, "Models cache directory <b>".$modelsCacheDir."</b> exists.");
					CacheManager::startProd($config);
					$files=CacheManager::getModelsFiles($config,true);
					foreach ($files as $file){
						$classname=ClassUtils::getClassFullNameFromFile($file);
						if(!CacheManager::modelCacheExists($classname)){
							$this->_addErrorMessage("warning", "The models cache file does not exists for the class <b>".$classname."</b>.");
						}else{
							$this->_addInfoMessage($infoIcon, "The models cache file for <b>".$classname."</b> exists.");
						}
					}
				}
			}

		}
	}
	protected function displayAllMessages($newStep=null){
		if($this->hasNoError()){
			$this->_addInfoMessage("checkmark", "everything is fine here");
		}
		if($this->hasMessages()){
			$messagesElmInfo=$this->displayModelsMessages($this->hasNoError()?"success":"info", $this->messages["info"]);
			echo $messagesElmInfo;
		}
		if($this->hasError()){
			$messagesElmError=$this->displayModelsMessages("error", $this->messages["error"]);
			echo $messagesElmError;
		}
		$this->showActions($newStep);
	}

	protected function showActions($newStep=null){
		$buttons=$this->jquery->semantic()->htmlButtonGroups("step-actions");
		$step=$this->getActiveModelStep();
		switch ($step[1]) {
			case "Conf":
				$buttons->addItem("Show config file")->getOnClick($this->_getAdminFiles()->getAdminBaseRoute()."/config","#action-response")->addIcon("settings");
				$buttons->addItem("Edit config file")->addClass("orange")->addIcon("edit");
			break;
			case "Connexion": case "Database":
				if($this->engineering==="reverse")
					$buttons->addItem("(Re-)Create database")->getOnClick($this->_getAdminFiles()->getAdminBaseRoute()."/createDb","#action-response")->addIcon("database");
			break;
			case "Models":
				if($this->engineering==="forward")
					$buttons->addItem("(Re-)Create models")->getOnClick($this->_getAdminFiles()->getAdminBaseRoute()."/createModels","#main-content",["attr"=>""])->addIcon("sticky note");
					$buttons->addItem("Classes diagram")->getOnClick($this->_getAdminFiles()->getAdminBaseRoute()."/_showAllClassesDiagram","#action-response",["attr"=>""])->addIcon("sticky note outline");
			break;
			case "Cache":
				$buttons->addItem("(Re-)Init models cache")->getOnClick($this->_getAdminFiles()->getAdminBaseRoute()."/_initModelsCache","#main-content")->addIcon("lightning");
			break;
		}
		$nextStep=$this->getNextModelStep();
		if(isset($nextStep)){
			$bt=$buttons->addItem($nextStep[1]);
			$bt->addIcon("angle double right",false);
			$bt->addLabel($nextStep[2],true,$nextStep[0]);
			$bt->getContent()[1]->addClass("green");
			if($this->hasNoError()){
				$bt->getOnClick($this->_getAdminFiles()->getAdminBaseRoute()."/_loadModelStep/".$this->engineering."/".($this->activeStep+1),"#models-main");
			}else{
				$bt->addClass("disabled");
			}
		}else{
			$bt=$buttons->addItem("See datas")->addClass("black");
			$bt->addIcon("unhide");
			if($this->hasNoError()){
				$bt->getOnClick($this->_getAdminFiles()->getAdminBaseRoute()."/models/noHeader/","#models-main");
			}else{
				$bt->addClass("disabled");
			}
		}
		echo "<div>".$buttons."</div><br>";
		echo "<div id='action-response'></div>";
	}

	protected function missingKeyInConfigMessage($message,$keys){
		if(\sizeof($keys)>0){
			$this->_addErrorMessage("warning", $message." : parameters <b>[".\Ajax\service\JArray::implode(",", $keys)."]</b> are required.");
			return true;
		}
		return false;
	}

	protected function _addErrorMessage($type,$content){
		$this->_addMessage("error", $type, $content);
	}

	protected function _addInfoMessage($type,$content){
		$this->_addMessage("info", $type, $content);
	}

	protected function _addMessage($key,$type,$content){
		$this->messages[$key][]=new InfoMessage($type, $content);
	}

	protected function hasError(){
		return \sizeof($this->messages["error"])>0;
	}

	protected function hasNoError(){
		return \sizeof($this->messages["error"])==0;
	}

	protected function hasMessages(){
		return \sizeof($this->messages["info"])>0;
	}

	protected function displayMessages($type,$messagesToDisplay,$header="",$icon=""){
		$messagesElm=$this->jquery->semantic()->htmlMessage("modelsMessages-".$type);
		$messagesElm->addHeader($header);
		if($this->hasError() && $type==="info")
			$messagesElm->setIcon("info circle");
			else
				$messagesElm->setIcon($icon);
				$messages=[];
				foreach ($messagesToDisplay as $msg){
					$elm=new HtmlSemDoubleElement("","li","",$msg->getContent());
					$elm->addIcon($msg->getType());
					$messages[]=$elm;
				}
				$messagesElm->addList($messages);
				$messagesElm->addClass($type);
				return $messagesElm;
	}
}
