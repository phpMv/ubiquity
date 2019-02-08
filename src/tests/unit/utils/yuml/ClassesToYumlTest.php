<?php
use Ubiquity\utils\yuml\ClassesToYuml;
use Ubiquity\cache\CacheManager;
use Ubiquity\cache\ClassUtils;
use Ubiquity\utils\base\UString;

/**
 * ClassesToYuml test case.
 */
class ClassesToYumlTest extends BaseTest {

	/**
	 *
	 * @var \Ubiquity\utils\yuml\ClassesToYuml
	 */
	private $classesToYuml;

	protected function _before() {
		parent::_before ();
		$this->classesToYuml = new ClassesToYuml ();
	}

	/**
	 * Tests ClassesToYuml->__toString()
	 */
	public function test__toString() {
		$ret = $this->classesToYuml->__toString ();
		$models = CacheManager::getModels ( $this->config );
		foreach ( $models as $model ) {
			$classname = ClassUtils::getClassSimpleName ( $model );
			$this->assertTrue ( UString::contains ( $classname, $ret ) );
		}
	}
}

