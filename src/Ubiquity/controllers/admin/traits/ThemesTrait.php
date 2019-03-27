<?php

namespace Ubiquity\controllers\admin\traits;

use Ubiquity\themes\ThemesManager;
use Ajax\semantic\html\collections\HtmlMessage;
use Ubiquity\utils\base\UString;
use Ubiquity\utils\http\URequest;

/**
 * @property \Ajax\JsUtils $jquery
 * @author jcheron <myaddressmail@gmail.com>
 *
 */
trait ThemesTrait {
	abstract public function showSimpleMessage($content, $type, $title=null,$icon = "info", $timeout = NULL, $staticName = null): HtmlMessage;
	abstract public function saveConfig() ;
	
	protected function refreshTheme($partial=true){
		$activeTheme=ThemesManager::getActiveTheme()??'no theme';
		$themes=ThemesManager::getAvailableThemes();
		$notInstalled=ThemesManager::getNotInstalledThemes();
		$this->jquery->getOnClick("._installTheme", "Admin/installTheme","#refresh-theme",["attr"=>"data-ajax"]);
		$this->loadView('@framework/Admin/themes/refreshTheme.html',compact('activeTheme','themes','notInstalled','partial'));
	}
	
	public function createNewTheme(){
		$themeName=$_POST["themeName"];
		$ubiquityCmd=$this->config["devtools-path"]??'Ubiquity';
		$allThemes=ThemesManager::getRefThemes();
		$extend="";
		if(array_search($_POST["extendTheme"], $allThemes)!==false){
			$extend=" -x=".trim($_POST["extendTheme"]);
		}
		$run=$this->runSilent("echo n | {$ubiquityCmd} create-theme ".$themeName.$extend,$output);
		echo $this->showConsoleMessage($output, "Theme creation",$hasError);
		if(!$hasError){
			if($run===false){
				echo $this->showSimpleMessage("Command executed with errors", "error","Theme creation","warning circle");
			}else{
				$msg=sprintf("Theme <b>%s</b> successfully created !",$themeName);
				if($extend!=null){
					$msg=sprintf("Theme <b>%s</b> based on <b>%s</b> successfully created !",$themeName,$extend);
				}
				echo $this->showSimpleMessage($msg, "success","Theme creation","check square outline");
			}
		}
		
		$this->jquery->getHref("._setTheme","#refresh-theme");
		$this->jquery->compile($this->view);
		$this->refreshTheme();
	}
	
	public function installTheme($themeName){
		$allThemes=ThemesManager::getRefThemes();
		$ubiquityCmd=$this->config["devtools-path"]??'Ubiquity';
		
		if(array_search($themeName, $allThemes)!==false){
			$run=$this->runSilent("echo n | {$ubiquityCmd} install-theme ".$themeName,$output);
			echo $this->showConsoleMessage($output, "Theme installation",$hasError);
			if(!$hasError){
				if($run===false){
					echo $this->showSimpleMessage("Command executed with errors", "error","Theme installation","warning circle");
				}else{
					$msg=sprintf("Theme <b>%s</b> successfully installed !",$themeName);
					echo $this->showSimpleMessage($msg, "success","Theme installation","check square outline");
				}
			}
			
		}
		$this->jquery->getHref("._setTheme","#refresh-theme");
		$this->jquery->compile($this->view);
		$this->refreshTheme();
	}
	
	public function setTheme($theme){
		$allThemes=ThemesManager::getAvailableThemes();
		if(array_search($theme, $allThemes)!==false){
			ThemesManager::setActiveTheme($theme);
			ThemesManager::saveActiveTheme($theme);
		}
		$this->jquery->getHref("._setTheme","#refresh-theme");
		$this->jquery->compile($this->view);
		$this->refreshTheme();
	}
	
	public function setDevtoolsPath(){
		$path=$_POST['path'];
		$this->config["devtools-path"]=$path;
		$this->saveConfig();
		echo $this->_checkDevtoolsPath($path);
		echo $this->jquery->compile();
	}
	
	public function _checkDevtoolsPath($path){
		$res=$this->runSilent($path.' version', $return_var);
		if(UString::contains('Ubiquity devtools', $return_var)){
			$res= $this->showConsoleMessage($return_var, "Ubiquity devtools", $hasError,"success","check square");
			$this->jquery->exec('$("._checkDevtools").toggleClass("green check square",true);$("._checkDevtools").toggleClass("red warning circle",false);$(".devtools-related").dimmer("hide");',true);
		}else{
			$res= $this->showSimpleMessage(sprintf("Devtools are not available at %s",$path),"error",'Devtools command path','warning circle');
			$this->jquery->exec('$("._checkDevtools").toggleClass("green check square",false);$("._checkDevtools").toggleClass("red warning circle",true);$(".devtools-related").dimmer("show").dimmer({closable:false});',true);
		}
		return $res;
	}
	
	private function getConsoleMessage($originalMessage){
		$messages=explode("\n",$originalMessage);
		$result=[];
		foreach ($messages as $msg){
			$msg=trim($msg);
			if(UString::startswith($msg, "·")){
				$result[]=$msg;
			}
		}
		return implode("<br>", $result);
	}
	
	private function showConsoleMessage($originalMessage,$title,&$hasError,$type='info',$icon='info circle'){
		$hasError=false;
		if($originalMessage!=null){
			if(UString::contains("error", $originalMessage)){
				$type="error";
				$icon="warning circle";
				$hasError=true;
			}
			return $this->showSimpleMessage($this->getConsoleMessage($originalMessage),$type,$title,$icon);
		}
	}
	
	protected function runSilent($command,&$return_var){
		ob_start();
		$res=system($command);
		$return_var=ob_get_clean();
		return $res;
	}
	
	public function _themeExists($fieldname) {
		if (URequest::isPost ()) {
			$result = [ ];
			header ( 'Content-type: application/json' );
			$theme = $_POST [$fieldname];
			$refThemes=ThemesManager::getRefThemes();
			$allThemes=array_merge($refThemes,ThemesManager::getAvailableThemes());
			$result ["result"] = (array_search($theme, $allThemes)===false);
			echo json_encode ( $result );
		}
	}

}

