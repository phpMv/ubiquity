<?php

namespace controllers;

use Ubiquity\controllers\SimpleViewController;

class TestSimpleViewController extends SimpleViewController {

	/**
	 *
	 * @route("/route/simple/(index/)?")
	 */
	public function index() {
		echo "Hello world!";
	}

	/**
	 *
	 * @route("/route/simple/withView/{p}")
	 */
	public function withView($p) {
		$this->loadView('TestSimpleViewController/simpleView.php', [ 'message' => $p ] );
	}

	/**
	 *
	 * @route("/route/simple/withViewString/{p}")
	 */
	public function withViewString($p) {
		echo $this->loadView('TestSimpleViewController/simpleView.php', [ 'message' => $p ] ,true);
	}
}

