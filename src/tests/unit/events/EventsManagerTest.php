<?php
use Ubiquity\events\DAOEvents;
use Ubiquity\events\EventsManager;
use Ubiquity\orm\DAO;
use Ubiquity\utils\base\UString;
use eventListener\GetOneEventListener;
use models\User;

/**
 * EventsManager test case.
 */
class EventsManagerTest extends BaseTest {

	/**
	 *
	 * @var \Ubiquity\events\EventsManager
	 */
	private $eventsManager;
	/**
	 *
	 * @var \Ubiquity\orm\DAO
	 */
	private $dao;

	/**
	 * Prepares the environment before running a test.
	 */
	protected function _before() {
		parent::_before ();
		$this->dao = new DAO ();
		$this->_loadConfig ();
		$this->_startCache ();
		$this->_startDatabase ( $this->dao );
		$this->eventsManager = new EventsManager ();
		$this->eventsManager->addListener ( DAOEvents::GET_ONE, GetOneEventListener::class );
		$this->eventsManager->store ();
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function _after() {
		$this->eventsManager = null;
	}

	/**
	 * Tests EventsManager::trigger()
	 */
	public function testTrigger() {
		ob_start ();
		DAO::getById ( User::class, 1, false );
		$res = ob_get_flush ();
		$this->assertTrue ( UString::contains ( User::class, $res ) );
		$this->assertTrue ( UString::contains ( "benjamin.sherman@gmail.com", $res ) );
	}
}

