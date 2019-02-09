<?php
use Ubiquity\utils\base\UFileSystem;

/**
 * UFileSystem test case.
 */
class UFileSystemTest extends BaseTest {
	private $testDir;
	/**
	 *
	 * @var Ubiquity\utils\base\UFileSystem
	 */
	private $uFileSystem;

	/**
	 * Prepares the environment before running a test.
	 */
	protected function _before() {
		parent::_before ();
		$this->uFileSystem = new UFileSystem ();
		$this->testDir = $this->uFileSystem->cleanFilePathname ( \ROOT . \DS . "tests-files/" );
		$this->uFileSystem->xcopy ( $this->uFileSystem->cleanFilePathname ( \ROOT . \DS . "files-tests/" ), $this->testDir );
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function _after() {
		$this->uFileSystem = null;
		parent::_after ();
	}

	/**
	 * Tests UFileSystem::glob_recursive()
	 */
	public function testGlob_recursive() {
		$files = $this->uFileSystem->glob_recursive ( $this->testDir . \DS . '*' );
		$this->assertEquals ( 4, sizeof ( $files ) );
	}

	/**
	 * Tests UFileSystem::deleteAllFilesFromFolder()
	 */
	public function testDeleteAllFilesFromFolder() {
		$files = $this->uFileSystem->glob_recursive ( $this->testDir . \DS . '*' );
		$this->assertEquals ( 4, sizeof ( $files ) );
		$this->uFileSystem->deleteAllFilesFromFolder ( $this->testDir );
		$files = $this->uFileSystem->glob_recursive ( $this->testDir . \DS . '*' );
		$this->assertEquals ( 2, sizeof ( $files ) );
		$this->uFileSystem->delTree ( $this->testDir );
		$files = $this->uFileSystem->glob_recursive ( $this->testDir . \DS . '*' );
		$this->assertEquals ( 0, sizeof ( $files ) );
	}

	/**
	 * Tests UFileSystem::deleteFile()
	 */
	public function testDeleteFile() {
		$files = $this->uFileSystem->glob_recursive ( $this->testDir . \DS . '*' );
		$this->assertEquals ( 4, sizeof ( $files ) );
		$this->uFileSystem->deleteFile ( $this->testDir . \DS . 'a.tmp' );
		$files = $this->uFileSystem->glob_recursive ( $this->testDir . \DS . '*' );
		$this->assertEquals ( 3, sizeof ( $files ) );
	}

	/**
	 * Tests UFileSystem::safeMkdir()
	 */
	public function testSafeMkdir() {
		// TODO Auto-generated UFileSystemTest::testSafeMkdir()
		$this->markTestIncomplete ( "safeMkdir test not implemented" );

		UFileSystem::safeMkdir(/* parameters */);
	}

	/**
	 * Tests UFileSystem::cleanPathname()
	 */
	public function testCleanPathname() {
		$notCleanPath = '/src//tests\/unit';
		$clean = $this->uFileSystem->cleanPathname ( $notCleanPath );
		$this->assertEquals ( \DS . 'src' . \DS . 'tests' . \DS . 'unit' . \DS, $clean );
	}

	/**
	 * Tests UFileSystem::cleanFilePathname()
	 */
	public function testCleanFilePathname() {
		$notCleanPath = '/src//tests\/unit\file.php';
		$clean = $this->uFileSystem->cleanFilePathname ( $notCleanPath );
		$this->assertEquals ( \DS . 'src' . \DS . 'tests' . \DS . 'unit' . \DS . 'file.php', $clean );
	}

	/**
	 * Tests UFileSystem::tryToRequire()
	 */
	public function testTryToRequire() {
		// TODO Auto-generated UFileSystemTest::testTryToRequire()
		$this->markTestIncomplete ( "tryToRequire test not implemented" );

		UFileSystem::tryToRequire(/* parameters */);
	}

	/**
	 * Tests UFileSystem::lastModified()
	 */
	public function testLastModified() {
		// TODO Auto-generated UFileSystemTest::testLastModified()
		$this->markTestIncomplete ( "lastModified test not implemented" );

		UFileSystem::lastModified(/* parameters */);
	}

	/**
	 * Tests UFileSystem::load()
	 */
	public function testLoad() {
		// TODO Auto-generated UFileSystemTest::testLoad()
		$this->markTestIncomplete ( "load test not implemented" );

		UFileSystem::load(/* parameters */);
	}

	/**
	 * Tests UFileSystem::getDirFromNamespace()
	 */
	public function testGetDirFromNamespace() {
		$dir = $this->uFileSystem->getDirFromNamespace ( "Ubiquity\\unit\\test" );
		$this->assertEquals ( \ROOT . \DS . 'Ubiquity' . \DS . 'unit' . \DS . 'test', $dir );
	}

	/**
	 * Tests UFileSystem::delTree()
	 */
	public function testDelTree() {
		$files = $this->uFileSystem->glob_recursive ( $this->testDir . \DS . '*' );
		$this->assertEquals ( 4, sizeof ( $files ) );
		UFileSystem::delTree ( $this->testDir );
		$files = $this->uFileSystem->glob_recursive ( $this->testDir . \DS . '*' );
		$this->assertEquals ( 0, sizeof ( $files ) );
	}

	/**
	 * Tests UFileSystem::getLines()
	 */
	public function testGetLines() {
		$lines = $this->uFileSystem->getLines ( $this->testDir . \DS . 'a.tmp', false, null, function (&$result, $line) {
			$result [] = trim ( $line, "\n" );
		} );
		$this->assertEquals ( 4, sizeof ( $lines ) );
		$this->assertEquals ( "a", $lines [0] );
		$this->assertEquals ( "aa", $lines [1] );
		$this->assertEquals ( "aaa", $lines [2] );
		$this->assertEquals ( 'aaaa', $lines [3] );
		// get lines reverse
		$lines = $this->uFileSystem->getLines ( $this->testDir . \DS . 'a.tmp', true, null, function (&$result, $line) {
			$result [] = trim ( $line, "\n" );
		} );
		$this->assertEquals ( 4, sizeof ( $lines ) );
		$this->assertEquals ( "a", $lines [3] );
		$this->assertEquals ( "aa", $lines [2] );
		$this->assertEquals ( "aaa", $lines [1] );
		$this->assertEquals ( 'aaaa', $lines [0] );
		// get 2 lines
		$lines = $this->uFileSystem->getLines ( $this->testDir . \DS . 'a.tmp', false, 2, function (&$result, $line) {
			$result [] = trim ( $line, "\n" );
		} );
		$this->assertEquals ( 2, sizeof ( $lines ) );
		$this->assertEquals ( "a", $lines [0] );
		$this->assertEquals ( "aa", $lines [1] );
		// get 2 lines reverse
		$lines = $this->uFileSystem->getLines ( $this->testDir . \DS . 'a.tmp', true, 2, function (&$result, $line) {
			$result [] = trim ( $line, "\n" );
		} );
		$this->assertEquals ( 2, sizeof ( $lines ) );
		$this->assertEquals ( "aaa", $lines [1] );
		$this->assertEquals ( 'aaaa', $lines [0] );
	}
}

