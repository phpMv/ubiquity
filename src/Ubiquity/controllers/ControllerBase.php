<?php

namespace Ubiquity\controllers;

use Ubiquity\utils\http\URequest;

/**
 * Default controller used in a new project.
 * Ubiquity\controllers$ControllerBase
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.3
 *
 */
abstract class ControllerBase extends Controller {
	protected string $headerView = '@activeTheme/main/vHeader.html';
	protected string $footerView = '@activeTheme/main/vFooter.html';

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

