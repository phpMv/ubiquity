<?php

namespace controllers;

use Ubiquity\controllers\Controller;

class TestController extends Controller {

	/**
	 *
	 * @route("/route/test/(index/)?")
	 * {@inheritdoc}
	 * @see \Ubiquity\controllers\Controller::index()
	 */
	public function index() {
		echo "Hello world!";
	}

	public function test() {
		echo "test!";
	}

	public function doForward() {
		echo "forward!";
	}

	/**
	 *
	 * @route("/route/test/withView/{p}", "name"=>"withView")
	 */
	public function withView($p) {
		$this->loadDefaultView ( [ 'message' => $p ] );
	}

	public function redirectToWithView() {
		$this->redirectToRoute ( "withView", [ 'redirection' ] );
	}

	public function forwardToWithView() {
		$this->forward ( self::class, 'withView', [ 'redirection2' ] );
	}
}

