<?php

namespace Ubiquity\controllers;

use Ubiquity\utils\http\URequest;

abstract class ControllerBase extends Controller {
	protected $headerView="@framework/main/vHeader.html";
	protected $footerView="@framework/main/vFooter.html";

	public function initialize() {
		if (!URequest::isAjax()) {
			$this->loadView($this->headerView);
		}
	}

	public function finalize() {
		if (!URequest::isAjax()) {
			$this->loadView($this->footerView);
		}
	}
}

