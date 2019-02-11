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
}

