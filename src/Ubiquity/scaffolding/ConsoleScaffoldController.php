<?php

namespace Ubiquity\scaffolding;

class ConsoleScaffoldController extends ScaffoldController {
	private $activeDir;

	public function __construct($activeDir) {
		$this->activeDir = $activeDir;
	}

	protected function storeControllerNameInSession($controller) {
	}

	protected function showSimpleMessage($content, $type, $title = null, $icon = "info", $timeout = NULL, $staticName = null) {
		return strip_tags ( $content );
	}

	protected function getTemplateDir() {
		return $this->activeDir . "/project-files/templates/";
	}

	protected function _addMessageForRouteCreation($path, $jsCallback = "") {
		echo "You need to re-init Router cache to apply this update with init-cache command\n";
	}
}

