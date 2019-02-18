<?php
use Ubiquity\utils\base\UDateTime;

/**
 * UDateTime test case.
 */
class UDateTimeTest extends BaseTest {

	/**
	 *
	 * @var UDateTime
	 */
	private $uDateTime;

	/**
	 * Prepares the environment before running a test.
	 */
	protected function _before() {
		parent::_before ();

		$this->uDateTime = new UDateTime ();
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function _after() {
		$this->uDateTime = null;

		parent::_after ();
	}

	/**
	 * Tests UDateTime::secondsToTime()
	 */
	public function testSecondsToTime() {
		$this->assertEquals ( "00:00:00", $this->uDateTime->secondsToTime ( 0 ) );
		$this->assertEquals ( "00:00:30", $this->uDateTime->secondsToTime ( 30 ) );
		$this->assertEquals ( "00:01:00", $this->uDateTime->secondsToTime ( 60 ) );
		$this->assertEquals ( "00:01:30", $this->uDateTime->secondsToTime ( 90 ) );
		$this->assertEquals ( "01:00:00", $this->uDateTime->secondsToTime ( 3600 ) );
		$this->assertEquals ( "01:00:15", $this->uDateTime->secondsToTime ( 3615 ) );
	}

	/**
	 * Tests UDateTime::mysqlDate()
	 */
	public function testMysqlDate() {
		$d = new DateTime ( '2001-12-29' );
		$this->assertEquals ( "2001-12-29", $this->uDateTime->mysqlDate ( $d ) );
	}

	/**
	 * Tests UDateTime::mysqlDateTime()
	 */
	public function testMysqlDateTime() {
		$d = new DateTime ( '2001-12-29' );
		$this->assertEquals ( "2001-12-29 00:00:00", $this->uDateTime->mysqlDateTime ( $d ) );
	}

	/**
	 * Tests UDateTime::longDate()
	 */
	public function testLongDate() {
		$d = $this->uDateTime->shortDatetime ( "2001-12-29", "fr" );
		// $this->assertEquals ( "samedi 29 décembre 2001", $this->uDateTime->shortDatetime ( "2001-12-29", "fr" ) );
	}

	/**
	 * Tests UDateTime::shortDate()
	 */
	public function testShortDate() {
		$d = $this->uDateTime->shortDate ( "2001-12-29", "fr" );
		// $this->assertEquals ( "29/12/2001", $this->uDateTime->shortDate ( "2001-12-29", "fr" ) );
	}

	/**
	 * Tests UDateTime::shortDatetime()
	 */
	public function testShortDatetime() {
		$d = $this->uDateTime->shortDatetime ( "2001-12-29", "fr" );
		// $this->assertEquals ( "29/12/2001 00:00:00", $this->uDateTime->shortDatetime ( "2001-12-29", "fr" ) );
	}

	/**
	 * Tests UDateTime::longDatetime()
	 */
	public function testLongDatetime() {
		$d = $this->uDateTime->shortDatetime ( "2001-12-29", "fr" );
		// $this->assertEquals ( "samedi 29 décembre 2001, 00:00:00", $this->uDateTime->shortDatetime ( "2001-12-29", "fr" ) );
	}
}

