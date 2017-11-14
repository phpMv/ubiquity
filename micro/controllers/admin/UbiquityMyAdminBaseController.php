<?php
namespace micro\controllers\admin;
use Ajax\service\JString;
use Ajax\semantic\html\elements\HtmlHeader;
use Ajax\semantic\html\elements\HtmlButton;
use micro\orm\DAO;
use micro\orm\OrmUtils;
use micro\orm\parser\Reflexion;
use micro\controllers\Startup;
use micro\controllers\Autoloader;
use micro\controllers\admin\UbiquityMyAdminData;
use controllers\ControllerBase;
use micro\utils\RequestUtils;
use Ajax\semantic\html\content\view\HtmlItem;
use micro\cache\CacheManager;
use micro\controllers\admin\popo\Route;
use micro\controllers\Router;
use micro\utils\StrUtils;
use micro\controllers\admin\popo\CacheFile;
use Ajax\semantic\html\collections\form\HtmlFormFields;
use micro\controllers\admin\popo\ControllerAction;
use Ajax\semantic\html\collections\form\HtmlForm;
use micro\orm\creator\ModelsCreator;
use micro\controllers\admin\traits\ModelsConfigTrait;
use micro\utils\FsUtils;
use micro\utils\yuml\ClassParser;
use micro\utils\yuml\Yuml;
use micro\utils\yuml\ClassesParser;

class UbiquityMyAdminBaseController extends ControllerBase{
	use ModelsConfigTrait;
	/**
	 * @var UbiquityMyAdminData
	 */
	private $adminData;

	/**
	 * @var UbiquityMyAdminViewer
	 */
	private $adminViewer;

	/**
	 * @var UbiquityMyAdminFiles
	 */
	private $adminFiles;

	private $globalMessage;


	public function initialize(){
		parent::initialize();
		if(RequestUtils::isAjax()===false){
			$semantic=$this->jquery->semantic();
			$elements=["UbiquityMyAdmin","Models","Routes","Controllers","Cache","Config"];
			$mn=$semantic->htmlMenu("mainMenu",$elements);
			$mn->getItem(0)->addClass("header")->addIcon("home big link");
			$mn->setPropertyValues("data-ajax", ["index","models","routes","controllers","cache","config"]);
			$mn->setActiveItem(0);
			$mn->setSecondary();
			$mn->getOnClick("Admin","#main-content",["attr"=>"data-ajax"]);
			$this->jquery->compile($this->view);
			$this->loadView($this->_getAdminFiles()->getViewHeader());
		}
	}

	public function index(){
		$semantic=$this->jquery->semantic();
		$items=$semantic->htmlItems("items");

		$items->fromDatabaseObjects($this->_getAdminViewer()->getMainMenuElements(), function($e){
			$item=new HtmlItem("");
			$item->addIcon($e[1]." bordered circular")->setSize("big");
			$item->addItemHeaderContent($e[0],[],$e[2]);
			$item->setProperty("data-ajax", \strtolower($e[0]));
			return $item;
		});
		$items->addClass("divided relaxed link");
		$items->getOnClick("Admin","#main-content",["attr"=>"data-ajax"]);
		$this->jquery->compile($this->view);
		$this->loadView($this->_getAdminFiles()->getViewIndex());
	}
	public function models($hasHeader=true){
		$semantic=$this->jquery->semantic();
		$header="";
		if($hasHeader===true){
			$header=$this->getHeader("models");
			$stepper=$this->_getModelsStepper();
		}
		if($this->_isModelsCompleted() || $hasHeader!==true){
			try {
				$dbs=$this->getTableNames();
				$menu=$semantic->htmlMenu("menuDbs");
				$menu->setVertical()->setInverted();
				foreach ($dbs as $table){
					$model=$this->getModelsNS()."\\".ucfirst($table);
					$file=\str_replace("\\", DS, ROOT . DS.$model).".php";
					$find=Autoloader::tryToRequire($file);
					if ($find){
						$count=DAO::count($model);
						$item=$menu->addItem(ucfirst($table));
						$item->addLabel($count);
						$item->setProperty("data-ajax", $table);
					}
				}
				$menu->getOnClick($this->_getAdminFiles()->getAdminBaseRoute()."/showTable","#divTable",["attr"=>"data-ajax"]);
				$menu->onClick("$('.ui.label.left.pointing.teal').removeClass('left pointing teal');$(this).find('.ui.label').addClass('left pointing teal');");

			} catch (\Exception $e) {
				$this->showSimpleMessage("Models cache is not created!&nbsp;", "error","warning circle",null,"errorMsg");
			}
			$this->jquery->compile($this->view);
			$this->loadView($this->_getAdminFiles()->getViewDataIndex());
		}else{
			echo $header;
			echo $stepper;
			echo "<div id='models-main'>";
			$this->_loadModelStep();
			echo "</div>";
			echo $this->jquery->compile($this->view);
		}
	}

	public function createModels($singleTable=null){
		$config=Startup::getConfig();
		\ob_start();
		ModelsCreator::create($config,false,$singleTable);
		$result=\ob_get_clean();
		$message=$this->showSimpleMessage("", "success","check mark",null,"msg-create-models");
		$message->addHeader("Models creation");
		$message->addList(\explode("\n", \str_replace("\n\n","\n", \trim($result))));
		$this->models(true);
		echo $message;
	}

	public function controllers(){
		$config=Startup::getConfig();
		$this->getHeader("controllers");
		$controllersNS=$config["mvcNS"]["controllers"];
		$controllersDir=ROOT . str_replace("\\", DS, $controllersNS);
		$this->showSimpleMessage("Controllers directory is <b>".FsUtils::cleanPathname($controllersDir)."</b>", "info","info circle",null,"msgControllers");
		$frm=$this->jquery->semantic()->htmlForm("frmCtrl");
		$frm->setValidationParams(["on"=>"blur","inline"=>true]);
		$input=$frm->addInput("name",null,"text","","Controller name")->addRules(["empty","regExp[/^[A-Za-z]\w*$/]"])->setWidth(6);
		$input->addAction("Create controller",true,"plus",true)->addClass("teal")->asSubmit();
		$frm->setSubmitParams($this->_getAdminFiles()->getAdminBaseRoute()."/createController","#main-content");
		$this->_getAdminViewer()->getControllersDataTable(ControllerAction::init());
		$this->jquery->postOnClick("._route[data-ajax]", $this->_getAdminFiles()->getAdminBaseRoute()."/routes","{filter:$(this).attr('data-ajax')}","#main-content");
		$this->jquery->execAtLast("$('#bt-controllers5CAdmin._clickFirst').click();");
		$this->addNavigationTesting();
		$this->jquery->compile($this->view);
		$this->loadView($this->_getAdminFiles()->getViewControllersIndex());
	}

	public function routes(){
		$config=Startup::getConfig();
		$this->getHeader("routes");
		$controllersNS=$config["mvcNS"]["controllers"];
		$routerCacheDir=ROOT . $config["cacheDirectory"].str_replace("\\", DS, $controllersNS);
		$this->showSimpleMessage("Router cache file is <b>".FsUtils::cleanPathname($routerCacheDir)."routes.cache.php</b>", "info","info circle",null,"msgRoutes");
		$routes=CacheManager::getRoutes();
		$this->_getAdminViewer()->getRoutesDataTable(Route::init($routes));
		$this->jquery->getOnClick("#bt-init-cache", $this->_getAdminFiles()->getAdminBaseRoute()."/initCacheRouter","#divRoutes");
		$this->jquery->postOnClick("#bt-filter-routes", $this->_getAdminFiles()->getAdminBaseRoute()."/filterRoutes","{filter:$('#filter-routes').val()}","#divRoutes",["ajaxTransition"=>"random"]);
		if(isset($_POST["filter"]))
			$this->jquery->exec("$(\"tr:contains('".$_POST["filter"]."')\").addClass('warning');",true);
		$this->addNavigationTesting();
		$this->jquery->compile($this->view);
		$this->loadView($this->_getAdminFiles()->getViewRoutesIndex(),["url"=>Startup::getConfig()["siteUrl"]]);
	}

	private function addNavigationTesting(){
		$this->jquery->postOnClick("._get", $this->_getAdminFiles()->getAdminBaseRoute()."/_runAction","{method:'get',url:$(this).attr('data-url')}","#modal");
		$this->jquery->postOnClick("._post", $this->_getAdminFiles()->getAdminBaseRoute()."/_runAction","{method:'post',url:$(this).attr('data-url')}","#modal");
		$this->jquery->postOnClick("._postWithParams", $this->_getAdminFiles()->getAdminBaseRoute()."/_runPostWithParams","{url:$(this).attr('data-url')}","#modal");
	}

	public function initCacheRouter(){
		$config=Startup::getConfig();
		\ob_start();
		CacheManager::initCache($config,"controllers");
		$message=\ob_get_clean();
		echo $this->showSimpleMessage(\nl2br($message), "info","info",4000);
		$routes=CacheManager::getRoutes();
		echo $this->_getAdminViewer()->getRoutesDataTable(Route::init($routes));
		echo $this->jquery->compile($this->view);

	}

	public function filterRoutes(){
		$filter=$_POST["filter"];
		$ctrls=[];
		if(StrUtils::isNotNull($filter)){
			$filter=\trim($_POST["filter"]);
			$ctrls=ControllerAction::initWithPath($filter);
			$routes=Router::filterRoutes($filter);
		}
		else $routes=CacheManager::getRoutes();
		echo $this->_getAdminViewer()->getRoutesDataTable(Route::init($routes));
		if(\sizeof($ctrls)>0){
			echo $this->_getAdminViewer()->getControllersDataTable($ctrls);
		}
		$this->addNavigationTesting();
		echo $this->jquery->compile($this->view);
	}

	public function cache(){
		$config=Startup::getConfig();
		$this->getHeader("cache");
		$this->showSimpleMessage("Cache directory is <b>".FsUtils::cleanPathname(ROOT.DS.$config["cacheDirectory"])."</b>", "info","info circle",null,"msgCache");
		$cacheFiles=CacheFile::init(ROOT . DS .$config["cacheDirectory"]."controllers", "Controllers");
		$cacheFiles=\array_merge($cacheFiles,CacheFile::init(ROOT . DS .$config["cacheDirectory"]."models", "Models"));
		$form=$this->jquery->semantic()->htmlForm("frmCache");
		$radios=HtmlFormFields::checkeds("cacheTypes[]",["controllers"=>"Controllers","models"=>"Models","views"=>"Views","queries"=>"Queries","annotations"=>"Annotations"],"Display cache types: ",["controllers","models"]);
		$radios->postFormOnClick($this->_getAdminFiles()->getAdminBaseRoute()."/setCacheTypes","frmCache","#dtCacheFiles tbody",["jqueryDone"=>"replaceWith"]);
		$form->addField($radios)->setInline();
		$this->_getAdminViewer()->getCacheDataTable($cacheFiles);
		$this->jquery->compile($this->view);
		$this->loadView($this->_getAdminFiles()->getViewCacheIndex());
	}

	public function setCacheTypes(){
		$config=Startup::getConfig();
		if(isset($_POST["cacheTypes"]))
			$caches=$_POST["cacheTypes"];
		else
			$caches=[];
		$cacheFiles=[];
		foreach ($caches as $cache){
			$cacheFiles=\array_merge($cacheFiles,CacheFile::init(ROOT . DS .$config["cacheDirectory"].$cache, \ucfirst($cache)));
		}
		$dt=$this->_getAdminViewer()->getCacheDataTable($cacheFiles);
		echo $dt->refresh();
		echo $this->jquery->compile($this->view);
	}

	public function deleteCacheFile(){
		if(isset($_POST["toDelete"])){
			$toDelete=$_POST["toDelete"];
			if(\file_exists($toDelete))
				\unlink($toDelete);
		}
		$this->setCacheTypes();
	}

	public function deleteAllCacheFiles(){
		if(isset($_POST["type"])){
			\session_destroy();
			$config=Startup::getConfig();
			$toDelete=$_POST["type"];
			CacheFile::delete(ROOT . DS .$config["cacheDirectory"].\strtolower($toDelete));
		}
		$this->setCacheTypes();
	}

	public function initCacheType(){
		if(isset($_POST["type"])){
			$type=$_POST["type"];
			$config=Startup::getConfig();
			switch ($type){
				case "Models":
					CacheManager::initCache($config,"models");
					break;
				case "Controllers":
					CacheManager::initCache($config,"controllers");
					break;
			}

		}
		$this->setCacheTypes();
	}

	public function _initModelsCache(){
		$config=Startup::getConfig();
		\ob_start();
		CacheManager::initCache($config,"models");
		\ob_end_clean();
		$this->models();
	}

	public function config($hasHeader=true){
		global $config;
		if($hasHeader===true)
			$this->getHeader("config");
		$this->_getAdminViewer()->getConfigDataElement($config);
		$this->jquery->compile($this->view);
		$this->loadView($this->_getAdminFiles()->getViewConfigIndex());
	}

	protected function getHeader($key){
		$semantic=$this->jquery->semantic();
		$header=$semantic->htmlHeader("header",3);
		$e=$this->_getAdminViewer()->getMainMenuElements()[$key];
		$header->asTitle($e[0],$e[2]);
		$header->addIcon($e[1]);
		$header->setBlock()->setInverted();
		return $header;
	}

	public function showTable($table){
		$this->_showTable($table);
		$model=$this->getModelsNS()."\\".ucfirst($table);
		$this->_getAdminViewer()->getModelsStructureDataTable(OrmUtils::getModelMetadata($model));
		$bt=$this->jquery->semantic()->htmlButton("btYuml","Class diagram");
		$bt->postOnClick($this->_getAdminFiles()->getAdminBaseRoute()."/_showDiagram/","{model:'".\str_replace("\\", "|", $model)."'}","#modal",["attr"=>""]);
		$this->jquery->exec('$("#models-tab .item").tab();',true);
		$this->jquery->compile($this->view);
		$this->loadView($this->_getAdminFiles()->getViewShowTable(),["classname"=>$model]);
	}

	public function _showDiagram($type="plain"){
		if(RequestUtils::isPost()){
			if(isset($_POST["model"])){
				$model=$_POST["model"];
				$model=\str_replace("|", "\\", $model);
				$modal=$this->jquery->semantic()->htmlModal("diagram","Class diagram : ".$model);
				$yuml=new ClassParser($model);
				$yuml->init(true, true);
				$modal->setContent($this->_getYumlImage($type, $yuml.""));
				$modal->addAction("Close");
				$this->jquery->exec("$('#diagram').modal('show');",true);
				echo $modal;
				echo $this->jquery->compile($this->view);
			}
		}
	}

	public function _showAllClassesDiagram($type="plain"){
		$yumlContent=new ClassesParser();
		echo $this->_getYumlImage($type, $yumlContent);
	}

	protected function _getYumlImage($type,$yumlContent){
		//return $yumlContent;
		return "<img src='http://yuml.me/diagram/".$type."/class/".$yumlContent."'>";
	}

	public function refreshTable(){
		$table=$_SESSION["table"];
		echo $this->_showTable($table);
		echo $this->jquery->compile($this->view);
	}

	public function showTableClick($tableAndId){
		$array=\explode(".", $tableAndId);
		if(\is_array($array)){
			$table=$array[0];
			$id=$array[1];
			$this->jquery->exec("$('#menuDbs .active').removeClass('active');$('.ui.label.left.pointing.teal').removeClass('left pointing teal active');$(\"[data-ajax='".$table."']\").addClass('active');$(\"[data-ajax='".$table."']\").find('.ui.label').addClass('left pointing teal');",true);
			$this->showTable($table);
			$this->jquery->exec("$(\"tr[data-ajax='".$id."']\").click();",true);
			echo $this->jquery->compile();
		}
	}

	public function createController($force=null){
		if(RequestUtils::isPost()){
			if(isset($_POST["name"]) && $_POST["name"]!==""){
				$config=Startup::getConfig();
				$controllersNS=$config["mvcNS"]["controllers"];
				$controllersDir=ROOT . DS . str_replace("\\", DS, $controllersNS);
				$controllerName=\ucfirst($_POST["name"]);
				$filename=$controllersDir.DS.$controllerName.".php";
				if(\file_exists($filename)===false){
					if(isset($config["mvcNS"]["controllers"]) && $config["mvcNS"]["controllers"]!=="")
						$namespace="namespace ".$config["mvcNS"]["controllers"].";";
					$this->openReplaceWrite(ROOT.DS."micro/controllers/admin/templates/controller.tpl", $filename, ["%controllerName%"=>$controllerName,"%indexContent%"=>"","%namespace%"=>$namespace]);
					$this->showSimpleMessage("The <b>".$controllerName."</b> controller has been created in <b>".$filename."</b>.", "success","checkmark circle",10000,"msgGlobal");
				}else{
					$this->showSimpleMessage("The file <b>".$filename."</b> already exists.<br>Can not create the <b>".$controllerName."</b> controller!", "warning","warning circle",10000,"msgGlobal");
				}
			}
		}
		$this->controllers();
	}

	public function _showFileContent(){
		if(RequestUtils::isPost()){
			$type=$_POST["type"];$filename=$_POST["filename"];
			if(\file_exists($filename)){
				$modal=$this->jquery->semantic()->htmlModal("file",$type." : ".\basename($filename));
				$frm=new HtmlForm("frmShowFileContent");
				$frm->addTextarea("file-content", null,\file_get_contents($filename),"",10);
				$modal->setContent($frm);
				$modal->addAction("Close");
				$this->jquery->exec("$('#file').modal('show');",true);
				echo $modal;
				echo $this->jquery->compile($this->view);
			}
		}
	}

	public function _runPostWithParams(){
		if(RequestUtils::isPost()){
			$url=$_POST["url"];
			$modal=$this->jquery->semantic()->htmlModal("response-with-params","Parameters for the POST:".$url);

			$frm=$this->jquery->semantic()->htmlForm("frmParams");
			$frm->addMessage("msg", "Enter your parameters.","Post parameters","info circle");
			$fields=$frm->addFields();
			$fields->addInput("name[]","Parameter name")->getDataField()->setIdentifier("name-1");
			$input=$fields->addInput("value[]","Parameter value");
			$input->getDataField()->setIdentifier("value-1");
			$input->addAction("",true,"remove")->addClass("icon basic _deleteParameter");
			$frm->addButton("clone", "Add parameter","yellow")->setTagName("div");
			if(isset($_COOKIE["post"]) && \sizeof($_COOKIE["post"])>0){
				$dd=$frm->addDropdownButton("btMem", "Memorized parameters",$_COOKIE["post"])->getDropdown()->setPropertyValues("data-mem", \array_map("addslashes",$_COOKIE["post"]));
				$cookiesIndex=\array_keys($_COOKIE["post"]);
				$dd->each(function($i,$item) use($cookiesIndex){
					$bt=new HtmlButton("bt-".$item->getIdentifier());
					$bt->asIcon("remove")->addClass("basic _deleteParam");
					$bt->getOnClick($this->_getAdminFiles()->getAdminBaseRoute()."/_deleteCookie",null,["attr"=>"data-value"]);
					$bt->setProperty("data-value", $cookiesIndex[$i]);
					$bt->onClick("$(this).parents('.item').remove();");
					$item->addContent($bt,true);
				});
				$this->jquery->click("[data-mem]","
						var objects=JSON.parse($(this).text());
						$.each(objects, function(name, value) {
							$('#clone').click();
							var inputs=$('.fields').last().find('input');
							inputs.first().val(name);
							inputs.last().val(value);
						});
						$('.fields').each(function(){
							var inputs=$(this).find('input');
							if(inputs.last().val()=='' && inputs.last().val()=='')
								if($('.fields').length>1)
									$(this).remove();
						});
						");
			}
			$this->jquery->click("._deleteParameter","
								if($('.fields').length>1)
									$(this).parents('.fields').remove();
					",true,true,true);
			$this->jquery->click("#clone","
					var cp=$('.fields').last().clone(true);
					var num = parseInt( cp.prop('id').match(/\d+/g), 10 ) +1;
					cp.find( '[id]' ).each( function() {
						var num = $(this).attr('id').replace( /\d+$/, function( strId ) { return parseInt( strId ) + 1; } );
						$(this).attr( 'id', num );
					});
					cp.insertBefore('#clone');");
			$frm->setValidationParams(["on"=>"blur","inline"=>true]);
			$frm->setSubmitParams($this->_getAdminFiles()->getAdminBaseRoute()."/_runAction","#modal",["params"=>"{method:'POST',url:'".$url."'}"]);
			$modal->setContent($frm);
			$modal->addAction("Validate");
			$this->jquery->click("#action-response-with-params-0","$('#frmParams').form('submit');",true,true,true);

			$modal->addAction("Close");
			$this->jquery->exec("$('.dimmer.modals.page').html('');$('#response-with-params').modal('show');",true);
			echo $modal;
			echo $this->jquery->compile($this->view);
		}
	}

	public function _deleteCookie($index,$type="post"){
		$name=$type."[".$index."]";
		if(isset($_COOKIE[$type][$index])){
			\setcookie($name,"",\time()-3600,"/","127.0.0.1");
		}
	}

	private function _setPostCookie($content){
		if(isset($_COOKIE["post"])){
			$cookieValues=\array_values($_COOKIE["post"]);
			if((\array_search($content, $cookieValues))===false){
				setcookie("post[".\sizeof($_COOKIE["post"])."]", $content,\time()+36000,"/","127.0.0.1");
			}
		}else{
			setcookie("post[0]", $content,\time()+36000,"/","127.0.0.1");
		}
	}

	private function _setGetCookie($index,$content){
		setcookie("get[".$index."]", $content,\time()+36000,"/","127.0.0.1");
	}

	public function _runAction($frm=null){
		if(RequestUtils::isPost()){
			$url=$_POST["url"];unset($_POST["url"]);
			$method=$_POST["method"];unset($_POST["method"]);
			$newParams=null;
			$postParams=$_POST;
			if(\sizeof($_POST)>0){
				if(\strtoupper($method)==="POST" && $frm!=="frmGetParams"){
					$postParams=[];
					$keys=$_POST["name"];
					$values=$_POST["value"];
					for($i=0;$i<\sizeof($values);$i++){
						if(JString::isNotNull($keys[$i]))
							$postParams[$keys[$i]]=$values[$i];
					}
					if(\sizeof($postParams)>0){
						$this->_setPostCookie(\json_encode($postParams));
					}
				}else{
					$newParams=$_POST;
					$this->_setGetCookie($url, \json_encode($newParams));
				}
			}
			$modal=$this->jquery->semantic()->htmlModal("response",\strtoupper($method).":".$url);
			$params=$this->getRequiredRouteParameters($url,$newParams);
			if(\sizeof($params)>0){
				$toPost=\array_merge($postParams,["method"=>$method,"url"=>$url]);
				$frm=$this->jquery->semantic()->htmlForm("frmGetParams");
				$frm->addMessage("msg", "You must complete the following parameters before continuing navigation testing","Required URL parameters","info circle");
				$paramsValues=$this->_getParametersFromCookie($url, $params);
				foreach ($paramsValues as $param=>$value){
					$frm->addInput($param,\ucfirst($param))->addRule("empty")->setValue($value);
				}
				$frm->setValidationParams(["on"=>"blur","inline"=>true]);
				$frm->setSubmitParams($this->_getAdminFiles()->getAdminBaseRoute()."/_runAction","#modal",["params"=>\json_encode($toPost)]);
				$modal->setContent($frm);
				$modal->addAction("Validate");
				$this->jquery->click("#action-response-0","$('#frmGetParams').form('submit');");
			}else{
				$this->jquery->ajax($method,$url,'#content-response.content',\json_encode($postParams));
			}
			$modal->addAction("Close");
			$this->jquery->exec("$('.dimmer.modals.page').html('');$('#response').modal('show');",true);
			echo $modal;
			echo $this->jquery->compile($this->view);
		}
	}

	private function _getParametersFromCookie($url,$params){
		$result=\array_fill_keys($params, "");
		if(isset($_COOKIE["get"])){
			if(isset($_COOKIE["get"][$url])){
				$values=\json_decode($_COOKIE["get"][$url],true);
				foreach ($params as $p){
					$result[$p]=@$values[$p];
				}
			}
		}
		return $result;
	}

	private function getRequiredRouteParameters(&$url,$newParams=null){
	    $url=stripslashes($url);
	    $route=Router::getRouteInfo($url);
	    if($route===false){
			$ns=Startup::getNS();
			$u=\explode("/", $url);
			$controller=$ns.$u[0];
			if(\sizeof($u)>1)
				$action=$u[1];
			else
				$action="index";
			if(isset($newParams) && \sizeof($newParams)>0){
				$url=$u[0]."/".$action."/".\implode("/", \array_values($newParams));
				return [];
			}
		}else{
			if(isset($newParams) && \sizeof($newParams)>0){
			    $routeParameters=$route["parameters"];
			    $i=0;
			    foreach ($newParams as $v){
			        if(isset($routeParameters[$i]))
			         $result[(int)$routeParameters[$i++]]=$v;
			    }
			    ksort($result);

			    $url=vsprintf(str_replace('(.+?)', '%s', $url), $result);
				/*foreach ($newParams as $param){
					$pos = strpos($url, "(.+?)");
					if ($pos !== false) {
						$url = substr_replace($url, $param, $pos, strlen("(.+?)"));
					}
				}*/
				return [];
			}
			$controller=$route["controller"];
			$action=$route["action"];
		}
		if(\class_exists($controller)){
			if(\method_exists($controller, $action)){
				$method=new \ReflectionMethod($controller,$action);
				return \array_map(function($e){return $e->name;},\array_slice($method->getParameters(),0,$method->getNumberOfRequiredParameters()));
			}
		}
		return [];
	}

	private function openReplaceWrite($source,$destination,$keyAndValues){
		if(file_exists($source)){
			$str=file_get_contents($source);
			array_walk($keyAndValues, function(&$item){if(is_array($item)) $item=implode("\n", $item);});
			$str=str_replace(array_keys($keyAndValues), array_values($keyAndValues), $str);
			return file_put_contents($destination,$str);
		}
	}

	protected function _showTable($table){
		$adminRoute=$this->_getAdminFiles()->getAdminBaseRoute();
		$_SESSION["table"]= $table;
		$semantic=$this->jquery->semantic();
		$model=$this->getModelsNS()."\\".ucfirst($table);

		$datas=DAO::getAll($model);
		$modal=($this->_getAdminViewer()->isModal($datas, $model)?"modal":"no");
		$lv=$semantic->dataTable("lv", $model, $datas);
		$attributes=$this->getFieldNames($model);

		$lv->setCaptions($this->_getAdminViewer()->getCaptions($attributes, $model));
		$lv->setFields($attributes);
		$lv->onPreCompile(function() use ($attributes,&$lv){
			$lv->getHtmlComponent()->colRight(\count($attributes));
		});

		$lv->setIdentifierFunction($this->getIdentifierFunction($model));
		$lv->getOnRow("click", $adminRoute."/showDetail","#table-details",["attr"=>"data-ajax"]);
		$lv->setUrls(["delete"=>$adminRoute."/delete","edit"=>$adminRoute."/edit/".$modal]);
		$lv->setTargetSelector(["delete"=>"#table-messages","edit"=>"#table-details"]);
		$lv->addClass("small very compact");
		$lv->addEditDeleteButtons(false,["ajaxTransition"=>"random"]);
		$lv->setActiveRowSelector("error");
		$this->jquery->getOnClick("#btAddNew", $adminRoute."/newModel/".$modal,"#table-details");
		return $lv;
	}

	protected function _edit($instance,$modal="no"){
		$_SESSION["instance"]=$instance;
		$modal=($modal=="modal");
		$form=$this->_getAdminViewer()->getForm("frmEdit",$instance);
		$this->jquery->click("#action-modal-frmEdit-0","$('#frmEdit').form('submit');",false);
		if(!$modal){
			$this->jquery->click("#bt-cancel","$('#form-container').transition('drop');");
			$this->jquery->compile($this->view);
			$this->loadView($this->_getAdminFiles()->getViewEditTable(),["modal"=>$modal]);
		}else{
			$this->jquery->exec("$('#modal-frmEdit').modal('show');",true);
			$form=$form->asModal(\get_class($instance));
			$form->setActions(["Okay","Cancel"]);
			$btOkay=$form->getAction(0);
			$btOkay->addClass("green")->setValue("Validate modifications");
			$form->onHidden("$('#modal-frmEdit').remove();");
			echo $form->compile($this->jquery,$this->view);
			echo $this->jquery->compile($this->view);
		}
	}

	public function edit($modal="no",$ids=""){
		$instance=$this->getModelInstance($ids);
		$instance->_new=false;
		$this->_edit($instance,$modal);
	}

	public function newModel($modal="no"){
		$model=$this->getModelsNS()."\\".ucfirst($_SESSION["table"]);
		$instance=new $model();
		$instance->_new=true;
		$this->_edit($instance,$modal);
	}

	public function update(){
		$message=$this->jquery->semantic()->htmlMessage("msgUpdate","Modifications were successfully saved","info");
		$instance=@$_SESSION["instance"];
		$className=\get_class($instance);
		$relations = OrmUtils::getManyToOneFields($className);
		$fieldTypes=OrmUtils::getFieldTypes($className);
		foreach ($fieldTypes as $property=>$type){
			if($type=="tinyint(1)"){
				if(isset($_POST[$property])){
					$_POST[$property]=1;
				}else{
					$_POST[$property]=0;
				}
			}
		}
		RequestUtils::setValuesToObject($instance,$_POST);
		foreach ($relations as $member){
			if($this->_getAdminData()->getUpdateManyToOneInForm()){
				$joinColumn=OrmUtils::getAnnotationInfoMember($className, "#joinColumn", $member);
				if($joinColumn){
					$fkClass=$joinColumn["className"];
					$fkField=$joinColumn["name"];
					if(isset($_POST[$fkField])){
						$fkObject=DAO::getOne($fkClass, $_POST["$fkField"]);
						Reflexion::setMemberValue($instance, $member, $fkObject);
					}
				}
			}
		}
		if(isset($instance)){
			if($instance->_new){
				$update=DAO::insert($instance);
			}else{
				$update=DAO::update($instance);
			}
			if($update){
				if($this->_getAdminData()->getUpdateManyToManyInForm()){
					$relations = OrmUtils::getManyToManyFields($className);
					foreach ($relations as $member){
						if(($annot=OrmUtils::getAnnotationInfoMember($className, "#manyToMany",$member))!==false){
							$newField=$member."Ids";
							$fkClass=$annot["targetEntity"];
							$fkObjects=DAO::getAll($fkClass,$this->getMultiWhere($_POST[$newField], $className));
							if(Reflexion::setMemberValue($instance, $member, $fkObjects)){
								DAO::insertOrUpdateManyToMany($instance, $member);
							}
						}
					}
				}
				$message->setStyle("success")->setIcon("checkmark");
				$this->jquery->get($this->_getAdminFiles()->getAdminBaseRoute()."/refreshTable","#lv","{}","",false,"replaceWith");
			}else{
				$message->setContent("An error has occurred. Can not save changes.")->setStyle("error")->setIcon("warning circle");
			}
			echo $message;
			echo $this->jquery->compile($this->view);
		}
	}

	private function getIdentifierFunction($model){
		$pks=$this->getPks($model);
		return function($index,$instance) use ($pks){
			$values=[];
			foreach ($pks as $pk){
				$getter="get".ucfirst($pk);
				if(method_exists($instance, $getter)){
					$values[]=$instance->{$getter}();
				}
			}
			return implode("_", $values);
		};
	}

	private function getPks($model){
		$instance = new $model();
		return OrmUtils::getKeyFields($instance);
	}

	private function getMultiWhere($ids,$class){
		$pk=OrmUtils::getFirstKey($class);
		$ids=explode(",", $ids);
		if(sizeof($ids)<1)
			return "";
		$strs=[];
		$idCount=\sizeof($ids);
		for($i=0;$i<$idCount;$i++){
			$strs[]=$pk."='".$ids[$i]."'";
		}
		return implode(" OR ", $strs);
	}

	private function getOneWhere($ids,$table){
		$ids=explode("_", $ids);
		if(sizeof($ids)<1)
			return "";
		$pks=$this->getPks(ucfirst($table));
		$strs=[];
		$idCount=\sizeof($ids);
		for($i=0;$i<$idCount;$i++){
			$strs[]=$pks[$i]."='".$ids[$i]."'";
		}
		return implode(" AND ", $strs);
	}

	private function getModelInstance($ids){
		$table=$_SESSION['table'];
		$model=$this->getModelsNS()."\\".ucfirst($table);
		$ids=\explode("_", $ids);
		return DAO::getOne($model,$ids);
	}

	public function delete($ids){
		$instance=$this->getModelInstance($ids);
		if(method_exists($instance, "__toString"))
			$instanceString=$instance."";
		else
			$instanceString=get_class($instance);
		if(sizeof($_POST)>0){
			if(DAO::remove($instance)){
				$message=$this->showSimpleMessage("Suppression de `".$instanceString."`", "info","info",4000);
				$this->jquery->exec("$('tr[data-ajax={$ids}]').remove();",true);
			}else{
				$message=$this->showSimpleMessage("Impossible de supprimer `".$instanceString."`", "warning","warning");
			}
		}else{
			$message=$this->showConfMessage("Confirmez la suppression de `".$instanceString."`?", "", $this->_getAdminFiles()->getAdminBaseRoute()."/delete/{$ids}", "#table-messages", $ids);
		}
		echo $message;
		echo $this->jquery->compile($this->view);
	}

	private function getFKMethods($model){
		$reflection=new \ReflectionClass($model);
		$publicMethods=$reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
		$result=[];
		foreach ($publicMethods as $method){
			$methodName=$method->getName();
			if(JString::startswith($methodName, "get")){
				$attributeName=lcfirst(JString::replaceAtFirst($methodName, "get", ""));
				if(!property_exists($model, $attributeName))
					$result[]=$methodName;
			}
		}
		return $result;
	}

	private function showSimpleMessage($content,$type,$icon="info",$timeout=NULL,$staticName=null){
		$semantic=$this->jquery->semantic();
		if(!isset($staticName))
			$staticName="msg-".rand(0,50);
		$message=$semantic->htmlMessage($staticName,$content,$type);
		$message->setIcon($icon." circle");
		$message->setDismissable();
		if(isset($timeout))
			$message->setTimeout(3000);
		return $message;
	}

	private function showConfMessage($content,$type,$url,$responseElement,$data,$attributes=NULL){
		$messageDlg=$this->showSimpleMessage($content, $type,"help circle");
		$btOkay=new HtmlButton("bt-okay","Confirmer","positive");
		$btOkay->addIcon("check circle");
		$btOkay->postOnClick($url,"{data:'".$data."'}",$responseElement,$attributes);
		$btCancel=new HtmlButton("bt-cancel","Annuler","negative");
		$btCancel->addIcon("remove circle outline");
		$btCancel->onClick($messageDlg->jsHide());

		$messageDlg->addContent([$btOkay,$btCancel]);
		return $messageDlg;
	}

	public function showDetail($ids){
		$viewer=$this->_getAdminViewer();
		$hasElements=false;
		$instance=$this->getModelInstance($ids);
		$table=$_SESSION['table'];
		$model=$this->getModelsNS()."\\".ucfirst($table);
		$relations = OrmUtils::getFieldsInRelations($model);
		$semantic=$this->jquery->semantic();
		$grid=$semantic->htmlGrid("detail");
		if(sizeof($relations)>0){
		$wide=intval(16/sizeof($relations));
		if($wide<4)
			$wide=4;
			foreach ($relations as $member){
				if(($annot=OrmUtils::getAnnotationInfoMember($model, "#oneToMany",$member))!==false){
					$objectFK=DAO::getOneToMany($instance, $member);
					$fkClass=$annot["className"];
				}elseif(($annot=OrmUtils::getAnnotationInfoMember($model, "#manyToMany",$member))!==false){
					$objectFK=DAO::getManyToMany($instance, $member);
					$fkClass=$annot["targetEntity"];
				}else{
					$objectFK=Reflexion::getMemberValue($instance, $member);
					if(isset($objectFK))
						$fkClass=\get_class($objectFK);
				}
				$fkTable=OrmUtils::getTableName($fkClass);
				$memberFK=$member;

				$header=new HtmlHeader("",4,$memberFK,"content");
				if(is_array($objectFK) || $objectFK instanceof \Traversable){
					$header=$viewer->getFkHeaderList($memberFK, $fkClass, $objectFK);
					$element=$viewer->getFkList($memberFK, $fkClass, $objectFK);
					foreach ($objectFK as $item){
						if(method_exists($item, "__toString")){
							$id=($this->getIdentifierFunction($fkClass))(0,$item);
							$item=$element->addItem($item."");
							$item->setProperty("data-ajax", $fkTable.".".$id);
							$item->addClass("showTable");
							$hasElements=true;
							$this->_getAdminViewer()->displayFkElementList($item, $memberFK, $fkClass, $item);
						}
					}
				}else{
					if(method_exists($objectFK, "__toString")){
						$header=$viewer->getFkHeaderElement($memberFK, $fkClass, $objectFK);
						$id=($this->getIdentifierFunction($fkClass))(0,$objectFK);
						$element=$viewer->getFkElement($memberFK, $fkClass, $objectFK);
						$element->setProperty("data-ajax", $fkTable.".".$id)->addClass("showTable");
					}
				}
				if(isset($element)){
					$grid->addCol($wide)->setContent($header.$element);
					$hasElements=true;
				}
			}
			if($hasElements)
				echo $grid;
			$this->jquery->getOnClick(".showTable", $this->_getAdminFiles()->getAdminBaseRoute()."/showTableClick","#divTable",["attr"=>"data-ajax","ajaxTransition"=>"random"]);
			echo $this->jquery->compile($this->view);
		}
	}

	protected function getModelsNS(){
		return Startup::getConfig()["mvcNS"]["models"];
	}

	protected function getUbiquityMyAdminData(){
		return new UbiquityMyAdminData();
	}

	protected function getUbiquityMyAdminViewer(){
		return new UbiquityMyAdminViewer($this);
	}

	protected function getUbiquityMyAdminFiles(){
		return new UbiquityMyAdminFiles();
	}

	private function getSingleton($value,$method){
		if(!isset($value)){
			$value=$this->$method();
		}
		return $value;
	}

	/**
	 * @return UbiquityMyAdminData
	 */
	public function _getAdminData(){
		return $this->getSingleton($this->adminData, "getUbiquityMyAdminData");
	}

	/**
	 * @return UbiquityMyAdminViewer
	 */
	public function _getAdminViewer(){
		return $this->getSingleton($this->adminViewer, "getUbiquityMyAdminViewer");
	}

	/**
	 * @return UbiquityMyAdminFiles
	 */
	public function _getAdminFiles(){
		return $this->getSingleton($this->adminFiles, "getUbiquityMyAdminFiles");
	}

	protected function getTableNames(){
		return $this->_getAdminData()->getTableNames();
	}

	protected function getFieldNames($model){
		return $this->_getAdminData()->getFieldNames($model);
	}
}

