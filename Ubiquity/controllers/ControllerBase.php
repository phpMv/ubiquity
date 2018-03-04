<?php

namespace Ubiquity\controllers;

use Ubiquity\utils\http\Request;

abstract class ControllerBase extends Controller {
	protected $headerView="main/vHeader.html";
	protected $footerView="main/vFooter.html";

	public function initialize() {
		if (!Request::isAjax()) {
			$this->loadView($this->headerView);
		}
	}

	public function finalize() {
		if (!Request::isAjax()) {
			$this->loadView($this->footerView);
		}
	}
}

