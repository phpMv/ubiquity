<?php

namespace Ubiquity\controllers\admin\traits;

use Ajax\JsUtils;
use Ubiquity\utils\http\URequest;
use Ajax\semantic\html\collections\menus\HtmlMenu;
use Ajax\semantic\html\modules\HtmlDropdown;
use Ubiquity\orm\creator\yuml\YumlModelsCreator;
use Ubiquity\controllers\Startup;
use Ubiquity\controllers\admin\UbiquityMyAdminFiles;

/**
 *
 * @author jc
 * @property JsUtils $jquery
 * @property View $view
 */
trait ModelsConfigTrait{
	use CheckTrait;

	abstract public function _getAdminData();

	abstract public function _getAdminViewer();

	/**
	 *
	 * @return UbiquityMyAdminFiles
	 */
	abstract public function _getFiles();
	private $activeStep=5;
	private $engineering="forward";
	private $steps=[ "forward" => [ [ "toggle on","Engineering","Forward" ],[ "settings","Conf","Database configuration" ],[ "database","Connexion","Database connexion" ],[ "sticky note","Models","Models generation" ],[ "lightning","Cache","Models cache generation" ] ],"reverse" => [ [ "toggle off","Engineering","Reverse" ],[ "sticky note","Models","Models configuration/implementation" ],[ "lightning","Cache","Models cache generation" ],[ "database plus","Database","Database creation" ] ] ];

	public function _getModelsStepper() {
		$this->_checkStep();
		$stepper=$this->jquery->semantic()->htmlStep("stepper");
		$stepper->setStartStep(1);
		$steps=$this->steps[$this->engineering];
		$count=\sizeof($steps);
		$completed=($this->_isModelsCompleted()) ? "completed" : "";
		for($index=0; $index < $count; $index++) {
			$step=$steps[$index];
			$step=$stepper->addStep($step);
			if ($index === 0) {
				$step->addClass("_noStep")->getOnClick($this->_getFiles()->getAdminBaseRoute() . "/_changeEngineering/" . $this->engineering . "/" . $completed, "#stepper", [ "jqueryDone" => "replaceWith","hasLoader" => false ]);
			} else {
				$step->setProperty("data-ajax", $index);
			}
		}
		$stepper->setActiveStep($this->activeStep);
		$_SESSION["step"]=$this->activeStep;
		$stepper->asLinks();
		$this->jquery->getOnClick(".step:not(._noStep)", $this->_getFiles()->getAdminBaseRoute() . "/_loadModelStep/" . $this->engineering . "/", "#models-main", [ "attr" => "data-ajax" ]);
		return $stepper;
	}

	public function _isModelsCompleted() {
		return \sizeof($this->steps[$this->engineering]) === $this->activeStep;
	}

	public function _changeEngineering($oldEngineering, $completed=null) {
		$this->engineering="forward";
		if ($oldEngineering === "forward") {
			$this->engineering="reverse";
		}
		$this->activeStep=\sizeof($this->getModelSteps());
		echo $this->_getModelsStepper();
		if ($completed !== "completed")
			$this->jquery->get($this->_getFiles()->getAdminBaseRoute() . "/_loadModelStep/" . $this->engineering . "/" . $this->activeStep, "#models-main");
		echo $this->jquery->compile($this->view);
	}

	protected function getModelSteps() {
		return $this->steps[$this->engineering];
	}

	protected function getActiveModelStep() {
		if (isset($this->getModelSteps()[$this->activeStep]))
			return $this->getModelSteps()[$this->activeStep];
			return end($this->steps[$this->engineering]);
	}

	protected function getNextModelStep() {
		$steps=$this->getModelSteps();
		$nextIndex=$this->activeStep + 1;
		if ($nextIndex < \sizeof($steps))
			return $steps[$nextIndex];
		return null;
	}

	public function _loadModelStep($engineering=null, $newStep=null) {
		if (isset($engineering))
			$this->engineering=$engineering;
		if (isset($newStep)) {
			$this->_checkStep($newStep);
			if ($newStep !== @$_SESSION["step"]) {
				if (isset($_SESSION["step"])) {
					$oldStep=$_SESSION["step"];
					$this->jquery->execAtLast('$("#item-' . $oldStep . '.step").removeClass("active");');
				}
			}
			$this->jquery->execAtLast('$("#item-' . $newStep . '.step").addClass("active");');
			$this->activeStep=$newStep;
			$_SESSION["step"]=$newStep;
		}

		$this->displayAllMessages();

		echo $this->jquery->compile($this->view);
	}

	public function _importFromYuml() {
		$yumlContent="[User|«pk» id:int(11);name:varchar(11)],[Groupe|«pk» id:int(11);name:varchar(11)],[User]0..*-0..*[Groupe]";
		$bt=$this->jquery->semantic()->htmlButton("bt-gen", "Generate models", "green fluid");
		$bt->postOnClick($this->_getFiles()->getAdminBaseRoute() . "/_generateFromYuml", "{code:$('#yuml-code').val()}", "#stepper", [ "attr" => "","jqueryDone" => "replaceWith" ]);
		$menu=$this->_yumlMenu("/_updateYumlDiagram", "{refresh:'true',code:$('#yuml-code').val()}", "#diag-class");
		$this->jquery->exec('$("#modelsMessages-success").hide()', true);
		$menu->compile($this->jquery, $this->view);
		$form=$this->jquery->semantic()->htmlForm("frm-yuml-code");
		$textarea=$form->addTextarea("yuml-code", "Yuml code", \str_replace(",", ",\n", $yumlContent . ""));
		$textarea->getField()->setProperty("rows", 20);
		$diagram=$this->_getYumlImage("plain", $yumlContent);
		$this->jquery->execOn("keypress","#yuml-code",'$("#yuml-code").prop("_changed",true);');
		$this->jquery->execAtLast('$("#yuml-tab .item").tab({onVisible:function(tab){
				if(tab=="diagram" && $("#yuml-code").prop("_changed")==true){
					'.$this->_yumlRefresh("/_updateYumlDiagram", "{refresh:'true',code:$('#yuml-code').val()}", "#diag-class").'
				}	
			}
		});');
		$this->jquery->compile($this->view);
		$this->loadView($this->_getFiles()->getViewYumlReverse(), [ "diagram" => $diagram ]);
	}

	public function _generateFromYuml() {
		if (URequest::isPost()) {
			$config=Startup::getConfig();
			$yumlGen=new YumlModelsCreator();
			$yumlGen->initYuml($_POST["code"]);
			\ob_start();
			$yumlGen->create($config);
			\ob_get_clean();
			Startup::forward($this->_getFiles()->getAdminBaseRoute() . "/_changeEngineering/completed");
		}
	}

	public function _updateYumlDiagram() {
		if (URequest::isPost()) {
			$type=$_POST["type"];
			$size=$_POST["size"];
			$yumlContent=$_POST["code"];
			$this->jquery->exec('$("#yuml-code").prop("_changed",false);',true);
			echo $this->_getYumlImage($type . $size, $yumlContent);
			echo $this->jquery->compile();
		}
	}
	
	private function _yumlRefresh($url="/_updateDiagram", $params="{}", $responseElement="#diag-class"){
		$params=JsUtils::_implodeParams([ "$('#frmProperties').serialize()",$params ]);
		return $this->jquery->postDeferred($this->_getFiles()->getAdminBaseRoute() . $url, $params, $responseElement, [ "ajaxTransition" => "random","attr" => "" ]);
	}

	private function _yumlMenu($url="/_updateDiagram", $params="{}", $responseElement="#diag-class", $type="plain", $size=";scale:100") {
		$params=JsUtils::_implodeParams([ "$('#frmProperties').serialize()",$params ]);
		$menu=new HtmlMenu("menu-diagram");
		$ddScruffy=new HtmlDropdown("ddScruffy", $type, [ "nofunky" => "Boring","plain" => "Plain","scruffy" => "Scruffy" ], true);
		$ddScruffy->setValue("plain")->asSelect("type");
		$this->jquery->postOn("change", "[name='type']", $this->_getFiles()->getAdminBaseRoute() . $url, $params, $responseElement, [ "ajaxTransition" => "random","attr" => "" ]);
		$menu->addItem($ddScruffy);
		$ddSize=new HtmlDropdown("ddSize", $size, [ ";scale:180" => "Huge",";scale:120" => "Big",";scale:100" => "Normal",";scale:80" => "Small",";scale:60" => "Tiny" ], true);
		$ddSize->asSelect("size");
		$this->jquery->postOn("change", "[name='size']", $this->_getFiles()->getAdminBaseRoute() . $url, $params, $responseElement, [ "ajaxTransition" => "random","attr" => "" ]);
		$menu->wrap("<form id='frmProperties' name='frmProperties'>", "</form>");
		$menu->addItem($ddSize);
		return $menu;
	}

	protected function displayModelsMessages($type, $messagesToDisplay) {
		$step=$this->getActiveModelStep();
		return $this->displayMessages($type, $messagesToDisplay, $step[2], $step[0]);
	}
}
