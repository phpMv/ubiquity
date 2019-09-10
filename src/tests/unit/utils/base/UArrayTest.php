<?php
use Ubiquity\utils\base\UArray;
use Ubiquity\utils\base\UString;

/**
 * UArray test case.
 */
class UArrayTest extends \Codeception\Test\Unit {
	private $assoArray;
	private $array;
	private $mixed;
	private $keys;

	/**
	 * Prepares the environment before running a test.
	 */
	protected function _before() {
		$this->assoArray = [ "a" => 1,"b" => 2,"c" => 3,"d" => 4 ];
		$this->array = [ 1,2,3,4 ];
		$this->mixed = [ 1,2,3,4,"a" => 1 ];
		$this->keys = [ "a","b","c","d" ];
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function _after() {
	}

	/**
	 * Tests UArray::isAssociative()
	 */
	public function testIsAssociative() {
		$this->assertTrue ( UArray::isAssociative ( $this->assoArray ) );
		$this->assertFalse ( UArray::isAssociative ( $this->array ) );
		$this->assertTrue ( UArray::isAssociative ( [ ] ) );
		$this->assertTrue ( UArray::isAssociative ( $this->mixed ) );
	}

	/**
	 * Tests UArray::extractKeys()
	 */
	public function testExtractKeys() {
		$this->assertEquals ( [ "a" => 1,"c" => 3 ], UArray::extractKeys ( $this->assoArray, [ "a","c" ] ) );
		$this->assertEquals ( [ "a" => 1,"c" => 3 ], UArray::extractKeys ( $this->assoArray, [ "a","c","g" ] ) );
		$this->assertEquals ( [ ], UArray::extractKeys ( $this->assoArray, [ ] ) );
		$this->assertEquals ( [ ], UArray::extractKeys ( [ ], [ ] ) );
		$this->assertEquals ( [ ], UArray::extractKeys ( $this->assoArray, [ ] ) );
		$this->assertEquals ( [ ], UArray::extractKeys ( $this->assoArray, [ "e","f" ] ) );
		$this->assertEquals ( [ ], UArray::extractKeys ( $this->array, [ "e","f" ] ) );
	}

	/**
	 * Tests UArray::getValue()
	 */
	public function testGetValue() {
		$this->assertEquals ( 1, UArray::getValue ( $this->assoArray, "a", 0 ) );
		$this->assertEquals ( 2, UArray::getValue ( $this->assoArray, "z", 1 ) );
		$this->assertNull ( UArray::getValue ( $this->assoArray, "z", 10 ) );
		$this->assertEquals ( 1, UArray::getValue ( $this->array, "a", 0 ) );
		$this->assertNull ( UArray::getValue ( $this->array, "z", 10 ) );
		$this->assertNull ( UArray::getValue ( [ ], "z", 10 ) );
	}

	/**
	 * Tests UArray::getRecursive()
	 */
	public function testGetRecursive() {
		$this->assertEquals ( 2, UArray::getRecursive ( $this->assoArray, "b" ) );
		$this->assertEquals ( 56, UArray::getRecursive ( [ "a" => [ "c" => 56 ] ], "c" ) );
		$this->assertEquals ( 123, UArray::getRecursive ( [ "a" => [ "c" => [ "d" => 123 ] ] ], "d" ) );
		$this->assertNull ( UArray::getRecursive ( $this->assoArray, "g" ) );
		$this->assertEquals ( 130, UArray::getRecursive ( $this->assoArray, "z", 130 ) );
		$this->assertNull ( UArray::getRecursive ( [ ], "g" ) );
		$this->assertNull ( UArray::getRecursive ( $this->array, "g" ) );
		$this->assertEquals ( 3, UArray::getRecursive ( $this->array, 2 ) );
	}

	/**
	 * Tests UArray::getDefaultValue()
	 */
	public function testGetDefaultValue() {
		$this->assertEquals ( 2, UArray::getDefaultValue ( $this->assoArray, "b", 50 ) );
		$this->assertEquals ( 50, UArray::getDefaultValue ( $this->assoArray, "z", 50 ) );
		$this->assertEquals ( 2, UArray::getDefaultValue ( $this->array, 1, 50 ) );
		$this->assertEquals ( 50, UArray::getDefaultValue ( $this->array, 300, 50 ) );
	}

	/**
	 * Tests UArray::asPhpArray()
	 */
	public function testAsPhpArray() {
		$this->assertEquals ( $this->assoArray, eval ( UArray::asPhpArray ( $this->assoArray, 'return array' ) . ";" ) );
	}

	/**
	 * Tests UArray::asJSON()
	 */
	public function testAsJSON() {
		$this->assertTrue ( UString::isJson ( Uarray::asJSON ( $this->assoArray ) ) );
	}

	/**
	 * Tests UArray::remove()
	 */
	public function testRemove() {
		$this->assertNotFalse ( \array_search ( 1, $this->assoArray ) );
		$this->assoArray = UArray::remove ( $this->assoArray, 1 );
		$this->assertFalse ( \array_search ( 1, $this->assoArray ) );
	}

	/**
	 * Tests UArray::removeByKey()
	 */
	public function testRemoveByKey() {
		$this->assertTrue ( isset ( $this->assoArray ['a'] ) );
		$this->assoArray = UArray::removeByKey ( $this->assoArray, 'a' );
		$this->assertFalse ( isset ( $this->assoArray ['a'] ) );
	}

	/**
	 * Tests UArray::removeRecursive()
	 */
	public function testRemoveRecursive() {
		// TODO Auto-generated UArrayTest::testRemoveRecursive()
		$this->markTestIncomplete ( "removeRecursive test not implemented" );

		UArray::removeRecursive(/* parameters */);
	}

	/**
	 * Tests UArray::removeByKeys()
	 */
	public function testRemoveByKeys() {
		// TODO Auto-generated UArrayTest::testRemoveByKeys()
		$this->markTestIncomplete ( "removeByKeys test not implemented" );

		UArray::removeByKeys(/* parameters */);
	}

	/**
	 * Tests UArray::removeOne()
	 */
	public function testRemoveOne() {
		// TODO Auto-generated UArrayTest::testRemoveOne()
		$this->markTestIncomplete ( "removeOne test not implemented" );

		UArray::removeOne(/* parameters */);
	}

	/**
	 * Tests UArray::update()
	 */
	public function testUpdate() {
		// TODO Auto-generated UArrayTest::testUpdate()
		$this->markTestIncomplete ( "update test not implemented" );

		UArray::update(/* parameters */);
	}

	/**
	 * Tests UArray::doubleBackSlashes()
	 */
	public function testDoubleBackSlashes() {
		// TODO Auto-generated UArrayTest::testDoubleBackSlashes()
		$this->markTestIncomplete ( "doubleBackSlashes test not implemented" );

		UArray::doubleBackSlashes(/* parameters */);
	}

	/**
	 * Tests UArray::iSearch()
	 */
	public function testISearch() {
		// TODO Auto-generated UArrayTest::testISearch()
		$this->markTestIncomplete ( "iSearch test not implemented" );

		UArray::iSearch(/* parameters */);
	}

	/**
	 * Tests UArray::iRemove()
	 */
	public function testIRemove() {
		// TODO Auto-generated UArrayTest::testIRemove()
		$this->markTestIncomplete ( "iRemove test not implemented" );

		UArray::iRemove(/* parameters */);
	}

	/**
	 * Tests UArray::iRemoveOne()
	 */
	public function testIRemoveOne() {
		// TODO Auto-generated UArrayTest::testIRemoveOne()
		$this->markTestIncomplete ( "iRemoveOne test not implemented" );

		UArray::iRemoveOne(/* parameters */);
	}
}

