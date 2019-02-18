<?php
use Ubiquity\utils\flash\FlashBag;
use Ubiquity\utils\flash\FlashMessage;

/**
 * FlashBag test case.
 */
class FlashBagTest extends BaseTest {

	/**
	 *
	 * @var FlashBag
	 */
	private $flashBag;

	/**
	 * Prepares the environment before running a test.
	 */
	protected function _before() {
		parent::_before ();
		$this->flashBag = new FlashBag ();
		$this->flashBag->addMessage ( "message1", "t1", "info", "info" );
		$this->flashBag->addMessage ( "message2", "t2", "error", "error" );
		$this->flashBag->addMessage ( "message3", "t3", "info" );
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function _after() {
		$this->flashBag = null;
		parent::_after ();
	}

	/**
	 * Tests FlashBag->addMessage()
	 */
	public function testAddMessage() {
		$this->assertEquals ( 3, sizeof ( $this->flashBag->getAll () ) );
		$this->flashBag->addMessage ( "content", "title" );
		$this->assertEquals ( 4, sizeof ( $this->flashBag->getAll () ) );
	}

	/**
	 * Tests FlashBag->getMessages()
	 */
	public function testGetMessages() {
		$this->assertEquals ( 3, sizeof ( $this->flashBag->getAll () ) );
		$this->assertEquals ( 2, sizeof ( $this->flashBag->getMessages ( "info" ) ) );
		$this->assertEquals ( 1, sizeof ( $this->flashBag->getMessages ( "error" ) ) );
		$this->assertEquals ( 0, sizeof ( $this->flashBag->getMessages ( "notExists" ) ) );
	}

	/**
	 * Tests FlashBag->getAll()
	 */
	public function testGetAll() {
		$messages = $this->flashBag->getAll ();
		$this->assertInstanceOf ( FlashMessage::class, current ( $messages ) );
		$message = $messages [1];
		$this->assertEquals ( "message2", $message->getContent () );
		$this->assertEquals ( "t2", $message->getTitle () );
		$this->assertEquals ( "error", $message->getType () );
	}

	/**
	 * Tests FlashBag->clear()
	 */
	public function testClear() {
		$this->assertEquals ( 3, sizeof ( $this->flashBag->getAll () ) );
		$this->flashBag->clear ();
		$this->assertEquals ( 0, sizeof ( $this->flashBag->getAll () ) );
	}

	/**
	 * Tests FlashBag->rewind()
	 */
	public function testRewind() {
		$current = $this->flashBag->current ();
		$this->assertEquals ( 0, $this->flashBag->key () );
		$this->assertEquals ( "message1", $current->getContent () );

		$this->flashBag->rewind ();
		$this->assertEquals ( 0, $this->flashBag->key () );
		$this->assertEquals ( "message1", $this->flashBag->current ()->getContent () );
		$this->flashBag->next ();
		$this->assertEquals ( 1, $this->flashBag->key () );
		$this->assertEquals ( "message2", $this->flashBag->current ()->getContent () );

		$this->flashBag->rewind ();
		$this->assertEquals ( 0, $this->flashBag->key () );
		$this->assertEquals ( "message1", $this->flashBag->current ()->getContent () );
	}

	/**
	 * Tests FlashBag->valid()
	 */
	public function testValid() {
		$i = 0;
		while ( $this->flashBag->valid () ) {
			$current = $this->flashBag->current ();
			$this->flashBag->next ();
			$i ++;
		}
		$this->assertInstanceOf ( FlashMessage::class, $current );
		$this->assertEquals ( 3, $i );
	}

	/**
	 * Tests FlashBag->save()
	 */
	public function testSave() {
		$fb = new FlashBag ();
		$this->assertEquals ( 0, sizeof ( $fb->getAll () ) );
		$this->flashBag->save ();
		$fb = new FlashBag ();
		$this->assertEquals ( 3, sizeof ( $fb->getAll () ) );
	}
}

