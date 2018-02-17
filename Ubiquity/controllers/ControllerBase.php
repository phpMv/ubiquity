<?php

namespace Ubiquity\controllers;

use Ubiquity\utils\RequestUtils;

abstract class ControllerBase extends Controller {
	protected $headerView="main/vHeader.html";
	protected $footerView="main/vFooter.html";

	public function initialize() {
		if (!RequestUtils::isAjax()) {
			$this->loadView($this->headerView);
		}
	}

	public function finalize() {
		if (!RequestUtils::isAjax()) {
			$this->loadView($this->footerView);
		}
	}
}

