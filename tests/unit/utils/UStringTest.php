<?php
use Ubiquity\utils\base\UString;
class UStringTest extends \Codeception\Test\Unit {
	/**
	 *
	 * @var \UnitTester
	 */
	protected $tester;

	protected function _before() {
	}

	protected function _after() {
	}

	// tests
	public function testCleanAttribute() {
		$this->assertEquals ( "attr-ta-", UString::cleanAttribute ( "attr ta%%" ) );
		$this->assertEquals ( "attr", UString::cleanAttribute ( "attr" ) );
		$this->assertEquals ( "attr01", UString::cleanAttribute ( "attr01" ) );
		$this->assertEquals ( "01attr01", UString::cleanAttribute ( "01attr01" ) );
		$this->assertEquals ( "01attr", UString::cleanAttribute ( "01attr" ) );
		$this->assertEquals ( "attri-bute", UString::cleanAttribute ( "attri-bute" ) );
		$this->assertEquals ( "attri-bute", UString::cleanAttribute ( "attri--bute" ) );
		// $this->assertEquals ( "attri-bute", UString::cleanAttribute ( "attri---bute" ) );
	}

	public function testStartsWith() {
		$this->assertTrue ( UString::startswith ( '-test', '-' ) );
		$this->assertFalse ( UString::startswith ( 'test', '-' ) );
		$this->assertFalse ( UString::startswith ( '', '-' ) );
		$this->assertFalse ( UString::startswith ( null, '-' ) );
	}

	public function testEndsWith() {
		$this->assertTrue ( UString::endswith ( '-test', 't' ) );
		$this->assertFalse ( UString::endswith ( 'test', '-' ) );
		$this->assertFalse ( UString::endswith ( '', '-' ) );
		$this->assertFalse ( UString::endswith ( null, '-' ) );
	}
}