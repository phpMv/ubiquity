<?php
namespace micro\controllers\admin\traits;

use Ajax\JsUtils;
use Ajax\semantic\html\elements\HtmlLabel;
use micro\views\View;
use micro\utils\FsUtils;
use micro\utils\RequestUtils;
use micro\controllers\Startup;

/**
 * @author jc
 * @property JsUtils $jquery
 * @property View $view
 */
trait ControllersTrait{
	abstract public function _getAdminData();
	abstract public function _getAdminViewer();
	abstract public function _getAdminFiles();
	abstract public function controllers();
	abstract protected function openReplaceWrite($source,$destination,$keyAndValues);

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
						$msgView="";$indexContent="";
						if(isset($_POST["lbl-ck-div-name"])){
							$viewDir=ROOT.DS."views".DS.$controllerName.DS;
							FsUtils::safeMkdir($viewDir);
							$viewName=$viewDir.DS."index.html";
							$this->openReplaceWrite(ROOT.DS."micro/controllers/admin/templates/view.tpl", $viewName, ["%controllerName%"=>$controllerName,"%actionName%"=>"index"]);
							$msgView="<br>The default view associated has been created in <b>".FsUtils::cleanPathname(ROOT.DS.$viewDir)."</b>";
							$indexContent="\$this->loadview(\"".$controllerName."/index.html\");";
						}
						$this->openReplaceWrite(ROOT.DS."micro/controllers/admin/templates/controller.tpl", $filename, ["%controllerName%"=>$controllerName,"%indexContent%"=>$indexContent,"%namespace%"=>$namespace]);
						$this->showSimpleMessage("The <b>".$controllerName."</b> controller has been created in <b>".FsUtils::cleanPathname($filename)."</b>.".$msgView, "success","checkmark circle",30000,"msgGlobal");
				}else{
					$this->showSimpleMessage("The file <b>".$filename."</b> already exists.<br>Can not create the <b>".$controllerName."</b> controller!", "warning","warning circle",30000,"msgGlobal");
				}
			}
		}
		$this->controllers();
	}
	public function _createView(){
		if(RequestUtils::isPost()){
			$action=$_POST["action"];
			$controller=$_POST["controller"];
			$controllerFullname=$_POST["controllerFullname"];
			$viewName=$controller."/".$action.".html";
			FsUtils::safeMkdir(ROOT.DS."views".DS.$controller);
			$this->openReplaceWrite(ROOT.DS."micro/controllers/admin/templates/view.tpl", ROOT.DS."views".DS.$viewName, ["%controllerName%"=>$controller,"%actionName%"=>$action]);
			if(\file_exists(ROOT.DS."views".DS.$viewName)){
				$this->jquery->exec('$("#msgControllers").transition("show");$("#msgControllers .content").transition("show").append("<br><b>'.$viewName.'</b> created !");',true);
			}
			$r = new \ReflectionMethod($controllerFullname,$action);
			$lines = file($r->getFileName());
			$views=$this->_getAdminViewer()->getActionViews($controllerFullname,$controller,$action,$r,$lines);
			foreach ($views as $view){
				echo $view->compile($this->jquery);
				echo "&nbsp;";
			}
			echo $this->jquery->compile($this->view);
		}
	}
}
