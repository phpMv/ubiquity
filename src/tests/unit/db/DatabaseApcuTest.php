<?php
use Ubiquity\cache\system\ApcuCache;

/**
 * Database test case.
 *
 * @covers \Ubiquity\db\Database
 *
 */
class DatabaseApcuTest extends DatabaseTest {

	public function getCacheSystem() {
		return ApcuCache::class;
	}
}

