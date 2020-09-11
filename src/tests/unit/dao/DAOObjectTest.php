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
		parent::_before();
		CacheManager::start($this->config);
		CacheManager::initCache($this->config,'models',true);
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

