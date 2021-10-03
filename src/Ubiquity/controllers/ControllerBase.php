<?php

namespace Ubiquity\controllers;

use Ubiquity\utils\http\URequest;

/**
 * Default controller used in a new project.
 * Ubiquity\controllers$ControllerBase
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.1
 *
 */
abstract class ControllerBase extends Controller {
	protected $headerView = '@framework/main/vHeader.html';
	protected $footerView = '@framework/main/vFooter.html';

	public function initialize() {
		if (! URequest::isAjax ()) {
			$this->loadView ( $this->headerView );
		}
	}

	public function finalize() {
		if (! URequest::isAjax ()) {
			$this->loadView ( $this->footerView );
		}
	}
}

