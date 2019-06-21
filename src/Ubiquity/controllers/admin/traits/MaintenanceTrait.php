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
use Ubiquity\utils\base\UArray;

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
		return $this->jquery->renderView ( '@framework/Admin/maintenance/display.html', [ 'maintenance' => $maintenance ], true );
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
		$this->_createFrmMaintenance ( $maintenance );
	}

	public function _deleteMaintenanceById($idMaintenance) {
		$modes = $this->config ['maintenance'] ['modes'];
		if (isset ( $modes [$idMaintenance] )) {
			unset ( $this->config ['maintenance'] ['modes'] [$idMaintenance] );
			$this->saveConfig ();
		}
		$this->maintenance ();
	}

	public function hasMaintenance() {
		return is_string ( $this->config ['maintenance'] ['on'] ?? false);
	}
}

