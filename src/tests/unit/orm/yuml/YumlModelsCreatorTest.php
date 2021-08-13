<?php
use Ubiquity\orm\creator\yuml\YumlModelsCreator;
use Ubiquity\controllers\Startup;
use Ubiquity\cache\CacheManager;
use Ubiquity\orm\parser\Reflexion;
use Ubiquity\orm\OrmUtils;
use Ubiquity\utils\base\UFileSystem;

/**
 * YumlModelsCreator test case.
 */
class YumlModelsCreatorTest extends BaseTest {

	/**
	 *
	 * @var YumlModelsCreator
	 */
	private $yumlModelsCreator;

	/**
	 * Prepares the environment before running a test.
	 */
	protected function _before() {
		parent::_before ();
		Startup::setConfig ( $this->config );
		$this->_startCache ();
		$this->yumlModelsCreator = new YumlModelsCreator ();
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function _after() {
		$this->yumlModelsCreator = null;
		parent::_after ();
	}

	/**
	 * Tests YumlModelsCreator->initYuml()
	 */
	public function testInitYuml() {
		$yuml = '[TestConnection|-«pk» id:int(11);-dateCo:datetime;-url:varchar(255)],[TestGroupe|-«pk» id:int(11);-name:varchar(65);-email:varchar(255);-aliases:mediumtext],[TestOrganization|-«pk» id:int(11);-name:varchar(100);-domain:varchar(255);-aliases:text],[TestOrganizationsettings|-«pk» idTestSettings:int(11);-«pk» idTestOrganization:int(11);-value:varchar(100)],[TestSettings|-«pk» id:int(11);-name:varchar(45)],[TestUser|-«pk» id:int(11);-firstname:varchar(65);-lastname:varchar(65);-email:varchar(255);-password:varchar(255);-suspended:tinyint(1)],[TestOrganization]1-0..*[TestGroupe],[TestOrganization]1-0..*[TestOrganizationsettings],[TestOrganization]1-0..*[TestUser],[TestSettings]1-0..*[TestOrganizationsettings],[TestUser]1-0..*[TestConnection]';
		CacheManager::start($this->config);
		$this->yumlModelsCreator->initYuml ( $yuml );
		$this->yumlModelsCreator->create ( $this->config, false );
		//$this->assertTrue ( class_exists ( 'models\TestConnection', true ) );
		/*CacheManager::createOrmModelCache ( 'models\TestConnection' );
		CacheManager::getOrmModelCache ( 'models\TestConnection' );
		$this->assertEquals ( 'id', OrmUtils::getFirstKey ( 'models\TestConnection' ) );*/
		UFileSystem::deleteAllFilesFromFolder ( Startup::getModelsCompletePath (), 'Test*' );
	}
}

