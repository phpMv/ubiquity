<?php
use Ubiquity\cache\CacheManager;
use Ubiquity\controllers\Router;
use Ubiquity\orm\DAO;
abstract class BaseTest extends \Codeception\Test\Unit {
	protected $config;

	protected function _loadConfig() {
		$this->config = include 'tests/config/config.php';
	}

	protected function _startCache() {
		CacheManager::startProd ( $this->config );
	}

	protected function _startRouter($what = false) {
		if ($what == 'rest') {
			Router::startRest ();
		} elseif ($what == 'all') {
			Router::startAll ();
		} else {
			Router::start ();
		}
	}

	protected function _startDatabase(DAO $dao) {
		$db = $this->config ["database"] ?? [ ];
		if ($db ["dbName"] !== "") {
			$dao->connect ( $db ["type"], $db ["dbName"], $db ["serverName"] ?? DB_SERVER, $db ["port"] ?? 3306, $db ["user"] ?? 'root', $db ["password"] ?? '', $db ["options"] ?? [ ], $db ["cache"] ?? false);
		}
	}

	protected function _initRequest($path, $method = 'GET') {
		$_GET ["c"] = $path;
		$_SERVER ['REQUEST_METHOD'] = $method;
	}
}

