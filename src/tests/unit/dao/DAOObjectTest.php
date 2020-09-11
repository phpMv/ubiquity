<?php
use Ubiquity\orm\DAO;
use models\User;
use models\Organization;
use Ubiquity\db\Database;
use models\Groupe;
use Ubiquity\controllers\Startup;
use Ubiquity\cache\CacheManager;

require "DAOTest.php";

/**
 * DAO test case.
 */
class DAOObjectTest extends DAOTest {


	/**
	 * Prepares the environment before running a test.
	 */
	protected function _before() {
		if (! defined ( 'ROOT' )) {
			define ( 'ROOT', __DIR__ );
		}
		if (! defined ( 'DS' )) {
			define ( 'DS', DIRECTORY_SEPARATOR );
		}
		$this->config = include ROOT . 'config/config.php';
		CacheManager::start($this->config);
		CacheManager::initCache($this->config,'models',true);
		$this->_loadConfig();
		$this->dao = new DAO ();
		$this->_startCache ();
		$this->_startDatabase ( $this->dao );
		$this->dao->prepareGetById ( "orga", Organization::class );
		$this->dao->prepareGetOne ( "oneOrga", Organization::class, 'id= ?' );
		$this->dao->prepareGetAll ( "orgas", Organization::class );
	}

	protected function getCacheDirectory() {
		return 'cache-objects/';
	}
}

