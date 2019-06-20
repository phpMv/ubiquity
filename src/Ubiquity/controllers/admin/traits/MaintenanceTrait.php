<?php

namespace Ubiquity\controllers\admin\traits;

use Ajax\semantic\html\collections\HtmlMessage;
use Ajax\semantic\html\elements\HtmlButton;
use Ubiquity\controllers\admin\popo\MaintenanceMode;

/**
 *
 * @author jc
 * @property \Ajax\php\ubiquity\JsUtils $jquery
 * @property \Ubiquity\views\View $view
 * @property array $config
 */
trait MaintenanceTrait {

	abstract public function _getAdminData();

	abstract public function _getAdminViewer();

	abstract public function _getFiles();

	abstract public function loadView($viewName, $pData = NULL, $asString = false);

	abstract protected function showConfMessage($content, $type, $title, $icon, $url, $responseElement, $data, $attributes = NULL): HtmlMessage;

	abstract protected function showSimpleMessage($content, $type, $title = null, $icon = "info", $timeout = NULL, $staticName = null): HtmlMessage;

	protected function _displayActiveMaintenance(MaintenanceMode $maintenance) {
		$bt = $this->jquery->semantic ()->htmlButton ( 'bt-de-activate', 'Activate', 'fluid' );
		if ($maintenance->getActive ()) {
			$bt->setValue ( 'De-activate' );
			$bt->addClass ( 'blue' );
		} else {
			$bt->addClass ( 'red' );
		}
		$bt->getOnClick ( "/Admin/_activateMaintenance/" . $maintenance->getId (), "#main-content", [ 'hasLoader' => 'internal' ] );
		return $this->jquery->renderView ( '@framework/Admin/maintenance/display.html', [ 'maintenance' => $maintenance ], true );
	}

	public function _activateMaintenance($maintenanceId) {
		$this->_initCache ( 'controllers' );
		$maintenances = MaintenanceMode::manyFromArray ( $this->config ['maintenance'] );
		$maintenance = $maintenances [$maintenanceId];
		if (! $maintenance->getActive ()) {
			$maintenance->activate ();
			$this->config ['maintenance'] ['on'] = $maintenanceId;
		} else {
			$this->config ['maintenance'] ['on'] = false;
		}
		$this->saveConfig ();
		$this->maintenance ();
	}

	public function _displayMaintenance($maintenanceId) {
		$maintenances = MaintenanceMode::manyFromArray ( $this->config ['maintenance'] );
		$maintenance = $maintenances [$maintenanceId];
		$res = $this->_displayActiveMaintenance ( $maintenance );
		$this->jquery->renderView ( '@framework/Admin/main/component.html', [ 'compo' => $res ] );
	}
}

