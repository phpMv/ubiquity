<?php
use Ubiquity\utils\base\UString;

/**
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @covers \Ubiquity\utils\base\UString
 */
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
		$this->assertEquals ( 'true', UString::getBooleanStr ( 'true' ) );
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
		$this->assertTrue ( UString::isBooleanTrue ( 'on' ) );
		$this->assertTrue ( UString::isBooleanTrue ( '1' ) );
		$this->assertTrue ( UString::isBooleanTrue ( 1 ) );
		$this->assertTrue ( UString::isBooleanTrue ( true ) );

		$this->assertFalse ( UString::isBooleanTrue ( 'false' ) );
		$this->assertFalse ( UString::isBooleanTrue ( 'off' ) );
		$this->assertFalse ( UString::isBooleanTrue ( '0' ) );
		$this->assertFalse ( UString::isBooleanTrue ( 0 ) );
		$this->assertFalse ( UString::isBooleanTrue ( false ) );
	}

	public function testIsBooleanFalse() {
		$this->assertFalse ( UString::isBooleanFalse ( 'true' ) );
		$this->assertFalse ( UString::isBooleanFalse ( 'on' ) );
		$this->assertFalse ( UString::isBooleanFalse ( '1' ) );
		$this->assertFalse ( UString::isBooleanFalse ( 1 ) );
		$this->assertFalse ( UString::isBooleanFalse ( true ) );

		$this->assertTrue ( UString::isBooleanFalse ( 'false' ) );
		$this->assertTrue ( UString::isBooleanFalse ( 'off' ) );
		$this->assertTrue ( UString::isBooleanFalse ( '0' ) );
		$this->assertTrue ( UString::isBooleanFalse ( 0 ) );
		$this->assertTrue ( UString::isBooleanFalse ( false ) );
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
		$this->assertEquals ( 'aucun', UString::pluralize ( 0, 'aucun', '1 element', '{count} elements' ) );
		$this->assertEquals ( '1 element', UString::pluralize ( 1, 'aucun', '1 element', '{count} elements' ) );
		$this->assertEquals ( '5 elements', UString::pluralize ( 5, 'aucun', '1 element', '{count} elements' ) );
	}

	public function testFirstReplace() {
		$this->assertEquals ( 'popopipi', UString::firstReplace ( "pipopipi", "pi", "po" ) );
		$this->assertEquals ( 'Pipopipi', UString::firstReplace ( "pipopipi", "pi", "Pi" ) );
	}

	public function testReplaceFirstOccurrence() {
		$this->assertEquals ( 'popopipi', UString::replaceFirstOccurrence ( "pi", "po", "pipopipi" ) );
		$this->assertEquals ( 'popipopipi', UString::replaceFirstOccurrence ( "Pi", "pi", "popipoPipi" ) );
	}

	public function testReplaceArray() {
		$this->assertEquals ( 'papopa', UString::replaceArray ( 'pipopu', [ 'pi','pu' ], 'pa' ) );
	}

	public function testDoubleBackSlashes() {
		$this->assertEquals ( "models\\\\Client", UString::doubleBackSlashes ( "models\\Client" ) );
		$this->assertNull ( UString::doubleBackSlashes ( null ) );
		$this->assertEquals ( $this, UString::doubleBackSlashes ( $this ) );
	}

	public function testMask() {
		$this->assertEquals ( "*******", UString::mask ( "123abc*" ) );
		$this->assertEquals ( "*", UString::mask ( "*" ) );
		$this->assertEquals ( "---", UString::mask ( "123", "-" ) );
		$this->assertEquals ( "ABABAB", UString::mask ( "123", "AB" ) );
	}

	public function testIsValid() {
		$this->assertTrue ( UString::isValid ( "string" ) );
		$this->assertTrue ( UString::isValid ( 'string' ) );
		$this->assertTrue ( UString::isValid ( "" ) );
		$this->assertTrue ( UString::isValid ( 5 ) );
		$this->assertTrue ( UString::isValid ( false ) );
		$this->assertFalse ( UString::isValid ( null ) );
		$this->assertFalse ( UString::isValid ( $this ) );
	}

	public function testToString() {
		$this->assertEquals ( "15", UString::toString ( 15 ) );
		$this->assertEquals ( "0", UString::toString ( 0 ) );
		$this->assertEquals ( "", UString::toString ( false ) );
		$this->assertEquals ( "", UString::toString ( null ) );
		$this->assertEquals ( "", UString::toString ( $this ) );
		$this->assertEquals ( "quinze", UString::toString ( "quinze" ) );
	}
}