<?php

namespace Ubiquity\controllers\admin\traits;

use Ajax\semantic\html\collections\HtmlMessage;
use Ubiquity\controllers\admin\popo\MaintenanceMode;
use Ubiquity\cache\CacheManager;
use Ubiquity\controllers\admin\DefaultMaintenance;
use Ubiquity\controllers\Startup;
use Ubiquity\cache\ClassUtils;
use Ubiquity\controllers\admin\popo\ControllerAction;
use Ubiquity\utils\http\URequest;
use Ajax\semantic\components\validation\Rule;
use Ajax\semantic\html\base\constants\CheckboxType;

/**
 *
 * @author jc
 * @property \Ajax\php\ubiquity\JsUtils $jquery
 * @property \Ubiquity\views\View $view
 * @property array $config
 * @property \Ubiquity\scaffolding\AdminScaffoldController $scaffold
 */
trait MaintenanceTrait {
	protected $maintenanceControllers;

	abstract public function _getAdminData();

	abstract public function _getAdminViewer();

	abstract public function _getFiles();

	abstract public function loadView($viewName, $pData = NULL, $asString = false);

	abstract protected function showConfMessage($content, $type, $title, $icon, $url, $responseElement, $data, $attributes = NULL): HtmlMessage;

	abstract protected function showSimpleMessage($content, $type, $title = null, $icon = "info", $timeout = NULL, $staticName = null): HtmlMessage;

	protected function _displayActiveMaintenance(MaintenanceMode $maintenance) {
		$semantic = $this->jquery->semantic ();
		$baseRoute = $this->_getFiles ()->getAdminBaseRoute ();
		$bt = $semantic->htmlButton ( 'bt-de-activate', 'Activate', 'fluid' );
		if (! class_exists ( $maintenance->getController () )) {
			$this->_createNonExistingMaintenanceController ( $maintenance->getController () );
		}
		if ($maintenance->getActive ()) {
			$bt->setValue ( 'De-activate' );
			$bt->addClass ( 'blue' );
		} else {
			$bt->addClass ( 'red' );
		}
		$bt->getOnClick ( $baseRoute . "/_activateMaintenance/" . $maintenance->getId (), "#main-content", [ 'hasLoader' => 'internal' ] );
		$bt = $semantic->htmlButton ( 'bt-edit-maintenance', 'Edit...', 'fluid' );
		$bt->addIcon ( 'edit' );
		$bt->getOnClick ( $baseRoute . '/_frmMaintenance/' . $maintenance->getId (), '#maintenance', [ 'hasLoader' => 'internal','jsCallback' => '$("#maintenance-display-container").hide();','jqueryDone' => 'append' ] );
		return $this->jquery->renderView ( '@framework/Admin/maintenance/display.html', [ 'maintenance' => $maintenance,'selectedColor' => $maintenance->getActive () ? 'green' : '' ], true );
	}

	protected function getMaintenanceById($maintenanceId) {
		$maintenances = MaintenanceMode::manyFromArray ( $this->config ['maintenance'] );
		return $maintenances [$maintenanceId];
	}

	public function _activateMaintenance($maintenanceId) {
		$maintenance = $this->getMaintenanceById ( $maintenanceId );
		if (! $maintenance->getActive ()) {
			$this->config ['maintenance'] ['on'] = $maintenanceId;
			$this->_initCache ( 'controllers' );
			$maintenance->activate ();
		} else {
			$this->config ['maintenance'] ['on'] = false;
			$this->_initCache ( 'controllers' );
		}
		$this->saveConfig ();
		$this->maintenance ();
	}

	public function _displayMaintenance($maintenanceId) {
		$maintenance = $this->getMaintenanceById ( $maintenanceId );
		$res = $this->_displayActiveMaintenance ( $maintenance );
		$this->loadView ( '@framework/Admin/main/component.html', [ 'compo' => $res ] );
	}

	private function _addExcludedList($name, $elements) {
		$dd = $this->jquery->semantic ()->htmlDropdown ( 'dd-' . $name, implode ( ',', $elements ), array_combine ( $elements, $elements ) );
		$dd->asSearch ( $name, true, true );
		$dd->setAllowAdditions ( true );
	}

	public function _frmMaintenance($maintenanceId) {
		$maintenance = $this->getMaintenanceById ( $maintenanceId );
		$this->_createFrmMaintenance ( $maintenance );
	}

	protected function _createFrmMaintenance(MaintenanceMode $maintenance) {
		$baseRoute = $this->_getFiles ()->getAdminBaseRoute ();
		$this->_initMaintenanceController ();
		$frm = $this->jquery->semantic ()->htmlForm ( 'maintenance-frm' );
		$frm->addExtraFieldRules ( 'id', [
		'empty',[ 'checkMaintenanceId','Id {value} already exists!' ] ] );
		$frm->setValidationParams ( [ "on" => "blur","inline" => true ] );
		$frm->setSubmitParams ( $baseRoute . '/_submitMaintenanceForm', '#main-content', [ 'hasLoader' => 'internal' ] );

		$this->_addExcludedList ( 'hosts', $maintenance->getHosts () );
		$this->_addExcludedList ( 'urls', $maintenance->getUrls () );
		$this->_addExcludedList ( 'ports', $maintenance->getPorts () );
		$controllers = array_combine ( $this->maintenanceControllers, $this->maintenanceControllers );
		$ctrlList = $this->jquery->semantic ()->htmlDropdown ( "dd-controllers", "controllers\\MaintenanceController", $controllers );
		$ctrlList->asSelect ( "controller" );
		$ctrlList->setDefaultText ( "Select controller class" );
		$slider = $this->jquery->semantic ()->htmlCheckbox ( 'ck-active', 'Maintenance activation', 'on', CheckboxType::TOGGLE );
		$slider->setChecked ( $maintenance->getActive () );
		$this->jquery->change ( '#icon', '$(this).parent().find("i").attr("class","ui icon "+$(this).val());' );
		$this->jquery->postOn ( 'change', '#input-dd-controllers', $baseRoute . '/_getMaintenanceClassActions/', '{controller:$(this).val()}', '#actions', [ 'hasLoader' => false ] );
		$this->jquery->click ( '#cancel-btn', '$("#maintenance-frm-container").remove();$("#maintenance-display-container").show();' );
		$this->jquery->exec ( Rule::ajax ( $this->jquery, "checkMaintenanceId", $baseRoute . "/_checkMaintenanceId", "{}", "result=data.result;", "postForm", [ "form" => "maintenance-frm" ] ), true );
		$this->jquery->renderView ( '@framework/Admin/maintenance/form.html', [ 'maintenance' => $maintenance,'ports' => implode ( ',', $maintenance->getPorts () ),'urls' => implode ( ',', $maintenance->getUrls () ) ] );
	}

	protected function _initMaintenanceController() {
		$this->maintenanceControllers = CacheManager::getControllers ( DefaultMaintenance::class, false, false );
		if (sizeof ( $this->maintenanceControllers ) < 1) {
			$this->_createMaintenanceController ( 'MaintenanceController' );
			$this->maintenanceControllers = CacheManager::getControllers ( DefaultMaintenance::class, false, false );
		}
	}

	public function _createNonExistingMaintenanceController($name) {
		$class = ClassUtils::getClassSimpleName ( $name );
		$this->_createMaintenanceController ( $class );
	}

	private function _createMaintenanceController($name) {
		$ns = trim ( Startup::getNS ( "controllers" ), "\\" );
		$uses = "\nuse " . DefaultMaintenance::class . ';';
		return $this->scaffold->_createClass ( "class.tpl", $name, $ns, $uses, "extends DefaultMaintenance", "" );
	}

	public function _getMaintenanceClassActions() {
		$classname = $_POST ['controller'];
		$result = [ ];
		ControllerAction::initFromClassname ( $result, $classname );
		foreach ( $result as $controllerAction ) {
			echo '<option value="' . $controllerAction->getAction () . '">';
		}
	}

	public function _submitMaintenanceForm() {
		if (URequest::isPost ()) {
			$id = $_POST ['id'];
			$ref = $_POST ['ref'] ?? null;
			if ($ref != null && $ref != $id) {
				unset ( $this->config ['maintenance'] ['modes'] [$ref] );
				if ($this->config ['maintenance'] ['on'] == $ref) {
					$this->config ['maintenance'] ['on'] = $id;
				}
			}
			$checkExcluded = function ($v) {
				return \array_filter ( \explode ( ',', $v ) );
			};
			$_POST ['excluded'] ['urls'] = $checkExcluded ( $_POST ['urls'] );
			$_POST ['excluded'] ['ports'] = $checkExcluded ( $_POST ['ports'] );
			$_POST ['excluded'] ['hosts'] = $checkExcluded ( $_POST ['hosts'] );
			$this->config ['maintenance'] ['modes'] [$id] = $_POST;
			if (isset ( $_POST ['ck-active'] )) {
				$this->config ['maintenance'] ['on'] = $id;
				$this->_initCache ( 'controllers' );
			} else {
				if ($this->config ['maintenance'] ['on'] == $ref) {
					$this->config ['maintenance'] ['on'] = false;
					$this->_initCache ( 'controllers' );
				}
			}
			$this->saveConfig ();
		}
		$this->maintenance ();
	}

	public function _checkMaintenanceId() {
		if (URequest::isPost ()) {
			$result = [ ];
			header ( 'Content-type: application/json' );
			$newId = $_POST ["id"];
			$oldId = $_POST ["ref"];
			$maintenances = $this->config ['maintenance'] ['modes'];
			$result ["result"] = $newId == $oldId || ! isset ( $maintenances [$newId] );
			echo json_encode ( $result );
		}
	}

	public function _addNewMaintenanceType() {
		$maintenance = new MaintenanceMode ();
		$maintenance->setAction ( 'index' );
		$maintenance->setUntil ( ((new \DateTime ())->add ( new \DateInterval ( 'PT1H' ) ))->format ( 'Y-m-d\TH:i:s' ) );
		$this->_createFrmMaintenance ( $maintenance );
	}

	public function _deleteMaintenanceById($idMaintenance) {
		if (URequest::isPost ()) {
			$modes = $this->config ['maintenance'] ['modes'];
			if (isset ( $modes [$idMaintenance] )) {
				unset ( $this->config ['maintenance'] ['modes'] [$idMaintenance] );
				$this->saveConfig ();
			}
			$this->maintenance ();
		} else {
			$message = $this->showConfMessage ( "Do you confirm the deletion of `<b>" . $idMaintenance . "</b>`?", "error", "Remove confirmation", "question circle", $this->_getFiles ()->getAdminBaseRoute () . "/_deleteMaintenanceById/{$idMaintenance}", "#main-content", $idMaintenance );
			$this->jquery->renderView ( '@framework/Admin/main/component.html', [ 'compo' => $message ] );
		}
	}

	protected function hasMaintenance() {
		return is_string ( $this->config ['maintenance'] ['on'] ?? false);
	}

	protected function _smallMaintenanceActive($onMainPage, MaintenanceMode $maintenance) {
		$js = [ ];
		if (! $onMainPage) {
			$js = [ 'jsCallback' => '$("#maintenance-active").html("");' ];
		}
		$bt = $this->jquery->semantic ()->htmlButton ( "bt-stop-maintenance", null, 'blue small' );
		$bt->setProperty ( 'title', "Stop active maintenance" );
		$bt->asIcon ( 'stop' );
		echo "<div class='ui container' id='maintenance-active' style='display:inline;'>";
		echo $this->showSimpleMessage ( '<i class="ui icon ' . $maintenance->getIcon () . '"></i>&nbsp;<b>' . $maintenance->getId () . '</b> maintenance is active.&nbsp;' . $bt, 'compact inverted', null, null, '' );
		echo "&nbsp;</div>";
		$this->jquery->getOnClick ( "#bt-stop-maintenance", $this->_getFiles ()->getAdminBaseRoute () . "/_stopMaintenance/", '#maintenance-active', [ "dataType" => "html","attr" => "","hasLoader" => "internal" ] + $js );
	}

	public function _stopMaintenance() {
		$this->config ['maintenance'] ['on'] = false;
		$this->_initCache ( 'controllers' );
		$this->saveConfig ();
	}
}

