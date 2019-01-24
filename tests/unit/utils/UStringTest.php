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

	public function testGetBooleanStr() {
		$this->assertEquals ( 'true', UString::getBooleanStr ( true ) );
		$this->assertEquals ( 'true', UString::getBooleanStr ( 1 ) );
		$this->assertEquals ( 'true', UString::getBooleanStr ( 'a' ) );
		$this->assertEquals ( 'false', UString::getBooleanStr ( false ) );
		$this->assertEquals ( 'false', UString::getBooleanStr ( null ) );
		$this->assertEquals ( 'false', UString::getBooleanStr ( 0 ) );
	}

	public function testIsNull() {
		$this->assertTrue ( UString::isNull ( null ) );
		$this->assertTrue ( UString::isNull ( '' ) );
		$this->assertTrue ( UString::isNull ( "" ) );
		$this->assertFalse ( UString::isNull ( false ) );
		$this->assertFalse ( UString::isNull ( 55 ) );
		$this->assertFalse ( UString::isNull ( 'texte' ) );
	}

	public function testIsNotNull() {
		$this->assertFalse ( UString::isNotNull ( null ) );
		$this->assertFalse ( UString::isNotNull ( '' ) );
		$this->assertFalse ( UString::isNotNull ( "" ) );
		$this->assertTrue ( UString::isNotNull ( false ) );
		$this->assertTrue ( UString::isNotNull ( 55 ) );
		$this->assertTrue ( UString::isNotNull ( 'texte' ) );
	}

	public function testIsBooleanTrue() {
		$this->assertTrue ( UString::isBooleanTrue ( 'true' ) );
	}

	public function testIsBooleanFalse() {
		$this->assertTrue ( UString::isBooleanFalse ( 'false' ) );
	}

	public function testIsBoolean() {
		$this->assertTrue ( UString::isBoolean ( true ) );
		$this->assertTrue ( UString::isBoolean ( false ) );
		$this->assertFalse ( UString::isBoolean ( 0 ) );
		$this->assertFalse ( UString::isBoolean ( 1 ) );
		$this->assertFalse ( UString::isBoolean ( 'false' ) );
		$this->assertFalse ( UString::isBoolean ( 'true' ) );
	}

	public function testIsBooleanStr() {
		$this->assertFalse ( UString::isBooleanStr ( 'blop' ) );
		$this->assertFalse ( UString::isBooleanStr ( 'y' ) );
		$this->assertTrue ( UString::isBooleanStr ( 'true' ) );
	}

	public function testPluralize() {
	}

	public function testFirstReplace() {
	}

	public function testReplaceFirstOccurrence() {
	}

	public function testReplaceArray() {
	}

	public function testDoubleBackSlashes() {
	}

	public function testMask() {
	}

	public function testIsValid() {
	}

	public function testToString() {
	}
}