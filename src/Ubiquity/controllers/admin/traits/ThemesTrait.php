<?php

namespace Ubiquity\controllers\admin\traits;

use Ubiquity\themes\ThemesManager;
use Ajax\semantic\html\collections\HtmlMessage;
use Ubiquity\utils\base\UString;

/**
 * @property \Ajax\JsUtils $jquery
 * @author jcheron <myaddressmail@gmail.com>
 *
 */
trait ThemesTrait {
	abstract public function showSimpleMessage($content, $type, $title=null,$icon = "info", $timeout = NULL, $staticName = null): HtmlMessage;
	protected function refreshTheme($partial=true){
		$activeTheme=ThemesManager::getActiveTheme()??'no theme';
		$themes=ThemesManager::getAvailableThemes();
		$notInstalled=ThemesManager::getNotInstalledThemes();
		$this->jquery->getOnClick("._installTheme", "Admin/installTheme","#refresh-theme",["attr"=>"data-ajax"]);
		$this->loadView('@framework/Admin/themes/refreshTheme.html',compact('activeTheme','themes','notInstalled','partial'));
	}
	
	public function createNewTheme(){
		$themeName=$_POST["themeName"];
		$allThemes=ThemesManager::getRefThemes();
		$extend="";
		if(array_search($_POST["extendTheme"], $allThemes)!==false){
			$extend=" -x=".trim($_POST["extendTheme"]);
		}
		$run=$this->runSilent("echo n | Ubiquity create-theme ".$themeName.$extend,$output);
		$hasError=$this->showConsoleMessage($output, "Theme creation");
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
		
		$this->jquery->compile($this->view);
		$this->refreshTheme();
	}
	
	public function installTheme($themeName){
		$allThemes=ThemesManager::getRefThemes();
		
		if(array_search($themeName, $allThemes)!==false){
			$run=$this->runSilent("echo n | Ubiquity install-theme ".$themeName,$output);
			$hasError=$this->showConsoleMessage($output, "Theme installation");
			if(!$hasError){
				if($run===false){
					echo $this->showSimpleMessage("Command executed with errors", "error","Theme installation","warning circle");
				}else{
					$msg=sprintf("Theme <b>%s</b> successfully installed !",$themeName);
					echo $this->showSimpleMessage($msg, "success","Theme installation","check square outline");
				}
			}
			
		}
		$this->jquery->compile($this->view);
		$this->refreshTheme();
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
	
	private function showConsoleMessage($originalMessage,$title){
		$hasError=false;
		if($originalMessage!=null){
			$icon="info circle";
			$type="info";
			if(UString::contains("error", $originalMessage)){
				$type="error";
				$icon="warning circle";
				$hasError=true;
			}
			echo $this->showSimpleMessage($this->getConsoleMessage($originalMessage),$type,$title,$icon);
		}
		return $hasError;
	}
	
	private function runSilent($command,&$return_var){
		ob_start();
		$res=system($command);
		$return_var=ob_get_clean();
		return $res;
	}

}

