<?php
namespace micro\controllers\admin\traits;

use Ajax\JsUtils;
use micro\views\View;
use micro\db\Database;

/**
 * @author jc
 * @property JsUtils $jquery
 * @property View $view
 */
trait ModelsConfigTrait{
	use CheckTrait;
	abstract public function _getAdminData();
	abstract public function _getAdminViewer();
	/**
	 * @return UbiquityMyAdminFiles
	 */
	abstract public function _getAdminFiles();

	private $activeStep=5;
	private $engineering="forward";
	private $steps=["forward"=>[
			["toggle on","Engineering","Forward"],
			["settings","Conf","Database configuration"],
			["database","Connexion","Database connexion"],
			["sticky note","Models","Models generation"],
			["lightning","Cache","Models cache generation"]
		],"reverse"=>[
			["toggle off","Engineering","Reverse"],
			["sticky note","Models","Models configuration/implementation"],
			["lightning","Cache","Models cache generation"],
			["database plus","Database","Database creation"]
		]
	];


	public function _getModelsStepper(){
		$this->_checkStep();
		$stepper=$this->jquery->semantic()->htmlStep("stepper");
		$stepper->setStartStep(1);
		$steps=$this->steps[$this->engineering];
		$count=\sizeof($steps);
		$completed=($this->_isModelsCompleted())?"completed":"";
		for ($index=0;$index<$count;$index++){
			$step=$steps[$index];
			$step=$stepper->addStep($step);
			if($index===0){
				$step->addClass("_noStep")->getOnClick($this->_getAdminFiles()->getAdminBaseRoute()."/_changeEngineering/".$this->engineering."/".$completed,"#stepper",["jqueryDone"=>"replaceWith"]);
			}else{
				$step->setProperty("data-ajax", $index);
			}
		}
		$stepper->setActiveStep($this->activeStep);
		$_SESSION["step"]=$this->activeStep;
		$stepper->asLink();
		$this->jquery->getOnClick(".step:not(._noStep)", $this->_getAdminFiles()->getAdminBaseRoute()."/_loadModelStep/".$this->engineering."/","#models-main",["attr"=>"data-ajax"]);
		return $stepper;
	}

	public function _isModelsCompleted(){
		return \sizeof($this->steps[$this->engineering])===$this->activeStep;
	}

	public function _changeEngineering($oldEngineering,$completed=null){
		$this->engineering="forward";
		if($oldEngineering==="forward"){
			$this->engineering="reverse";
		}
		$this->activeStep=\sizeof($this->getModelSteps());
		echo $this->_getModelsStepper();
		if($completed!=="completed")
			$this->jquery->get($this->_getAdminFiles()->getAdminBaseRoute()."/_loadModelStep/".$this->engineering."/".$this->activeStep,"#models-main");
		echo $this->jquery->compile($this->view);
	}

	protected function getModelSteps(){
		return $this->steps[$this->engineering];
	}

	protected function getActiveModelStep(){
		return $this->getModelSteps()[$this->activeStep];
	}

	protected function getNextModelStep(){
		$steps=$this->getModelSteps();
		$nextIndex=$this->activeStep+1;
		if($nextIndex<\sizeof($steps))
			return $steps[$nextIndex];
		return null;
	}

	public function _loadModelStep($engineering=null,$newStep=null){
		if(isset($engineering))
			$this->engineering=$engineering;
		if(isset($newStep)){
			$this->_checkStep($newStep);
			if($newStep!==@$_SESSION["step"]){
				if(isset($_SESSION["step"])){
					$oldStep=$_SESSION["step"];
					$this->jquery->execAtLast('$("#item-'.$oldStep.'.step").removeClass("active");');
				}
			}
			$this->jquery->execAtLast('$("#item-'.$newStep.'.step").addClass("active");');
			$this->activeStep=$newStep;
			$_SESSION["step"]=$newStep;
		}

		$this->displayAllMessages($newStep);

		echo $this->jquery->compile($this->view);
	}

	protected function displayModelsMessages($type,$messagesToDisplay){
		$step=$this->getActiveModelStep();
		return $this->displayMessages($type,$messagesToDisplay,$step[2],$step[0]);
	}
}
