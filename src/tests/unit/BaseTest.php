<?php
use services\Service;
use Ubiquity\tests\unit\BaseUnitTest;
class BaseTest extends BaseUnitTest {

	protected function getDi() {
		return [ '@exec' => [ 'injected' => function ($controller) {
			return new Service ( $controller );
		} ] ];
	}

	protected function getDatabase() {
	}

	protected function getCacheDirectory() {
		return "cache-tests/";
	}
}

