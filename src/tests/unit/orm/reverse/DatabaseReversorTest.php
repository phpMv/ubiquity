<?php
use Ubiquity\orm\reverse\DatabaseReversor;
use Ubiquity\db\reverse\DbGenerator;
use Ubiquity\controllers\Startup;
use Ubiquity\utils\base\UString;
use Ubiquity\cache\CacheManager;

/**
 * DatabaseReversor test case.
 */
class DatabaseReversorTest extends BaseTest {
	/**
	 *
	 * @var \Ubiquity\orm\reverse\DatabaseReversor
	 */
	private $databaseReversor;

	protected function _before() {
		parent::_before ();
		Startup::setConfig ( $this->config );
		CacheManager::start($this->config);
		$this->databaseReversor = new DatabaseReversor ( new DbGenerator (),'default' );
	}
	
	protected function getCacheDirectory() {
		return "cache/";
	}

	/**
	 * Tests DatabaseReversor->createDatabase()
	 */
	public function testCreateDatabase() {
		/*$this->databaseReversor->createDatabase ( "testDb" );
		$script = $this->databaseReversor->__toString ();
		$this->assertTrue ( UString::contains ( "CREATE DATABASE", $script ) );*/
	}
}

