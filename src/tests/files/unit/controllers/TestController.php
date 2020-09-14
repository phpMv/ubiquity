<?php

namespace controllers;

use Ubiquity\controllers\Controller;
use Ubiquity\log\Logger;
use Ubiquity\log\libraries\UMonolog;

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

	public function throwError(){
		echo 15/0;
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

	/**
	 *
	 * @route("/route/test/assets/{p}", "name"=>"assets")
	 */
	public function assets($p) {
		$this->loadDefaultView ( [ 'message' => $p ] );
	}

	public function logs() {
		Logger::critical('logs', 'critical','part',(object)['id'=>15]);
		Logger::alert('logs', 'alert');
		Logger::log('info', 'logs', 'info');
	}
}

