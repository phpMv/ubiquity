<?php

namespace controllers;

use services\IService;
use Ubiquity\controllers\Controller;
use services\IInjected;
use Ubiquity\cache\CacheManager;
use Ubiquity\controllers\Startup;

/**
 * Controller TestDiController
 *
 * @property \services\IAllService allS
 */
class TestDiController extends Controller {
	/**
	 *
	 * @autowired
	 * @var IService
	 */
	private $iService;

	public function initCache() {
		$config = Startup::getConfig ();
		CacheManager::start ( $config );
		CacheManager::initCache ( $config );
	}

	/**
	 *
	 * @injected
	 * @var IInjected
	 */
	private $inj;

	public function index() {
	}

	public function autowired() {
		$this->iService->do ( "!:autowired:!" );
	}

	public function injected() {
		$this->inj->do ( "!:injected:!" );
	}

	public function allInjected() {
		$this->allS->do ( "!:*injected:!" );
	}
}
