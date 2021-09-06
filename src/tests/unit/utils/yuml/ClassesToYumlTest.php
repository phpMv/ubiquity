<?php
use Ubiquity\utils\yuml\ClassesToYuml;
use Ubiquity\cache\CacheManager;
use Ubiquity\cache\ClassUtils;
use Ubiquity\utils\base\UString;
use Ubiquity\controllers\Startup;
use Ubiquity\cache\system\ArrayCache;

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
	
	protected function getCacheDirectory() {
		return "cache/";
	}

	/**
	 * Tests ClassesToYuml->__toString()
	 */
	public function test__toString() {
		$config = Startup::getConfig ();
		$this->assertEquals ( ArrayCache::class, $config ['cache'] ['system'] );
		$this->classesToYuml = new ClassesToYuml ( 'default', true, true, true, true, true );
		$ret = $this->classesToYuml->__toString ();
		$models = CacheManager::getModels ( $this->config );
		foreach ( $models as $model ) {
			$classname = ClassUtils::getClassSimpleName ( $model );
			$this->assertTrue ( UString::contains ( $classname, $ret ) );
			$rClass = new ReflectionClass ( $model );
			$properties = $rClass->getProperties ();
			foreach ( $properties as $property ) {
				$this->assertTrue ( UString::contains ( $property->getName (), $ret ) );
			}
			$methods = $rClass->getMethods ();
			foreach ( $methods as $method ) {
				if ($method->isPublic ()) {
					$this->assertTrue ( UString::contains ( $method->getName (), $ret ) );
					$params = $method->getParameters ();
					foreach ( $params as $param ) {
						$this->assertTrue ( UString::contains ( $param->getName (), $ret ) );
					}
				}
			}
		}
	}

	/**
	 * Tests ClassesToYuml->parse()
	 */
	public function testParse() {
		$ret = $this->classesToYuml->__toString ();
		$models = CacheManager::getModels ( $this->config );
		foreach ( $models as $model ) {
			$classname = ClassUtils::getClassSimpleName ( $model );
			$this->assertTrue ( UString::contains ( $classname, $ret ) );
			$rClass = new ReflectionClass ( $model );
			$properties = $rClass->getProperties ();
			foreach ( $properties as $property ) {
				$this->assertTrue ( UString::contains ( $property->getName (), $ret ) );
			}
		}
	}
}

