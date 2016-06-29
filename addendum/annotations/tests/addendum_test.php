<?php
	require_once('simpletest/autorun.php');
	require_once(dirname(__FILE__).'/../../annotations.php');

	class SimpleClass extends Annotation {}
	class Namespace_ClassWithNamespace extends Annotation {}
	class Namespace1_CommonSuffix extends Annotation {}
	class Namespace2_CommonSuffix extends Annotation {}
	class Namespace_Annotation_AClass extends Annotation {}
	class AClass {}

	
	class TestOfAddendum extends UnitTestCase {
		public function testClassResolverShouldFindClassBasedOnName() {
			$this->assertEqual(Addendum::resolveClassName('SimpleClass'), 'SimpleClass');
		}	
		public function testClassResolverShouldFindClassBasedOnSuffix() {
			$this->assertEqual(Addendum::resolveClassName('ClassWithNamespace'), 'Namespace_ClassWithNamespace');
			$this->assertEqual(Addendum::resolveClassName('WithNamespace'), 'WithNamespace');
			$this->assertEqual(Addendum::resolveClassName('Annotation_AClass'), 'Namespace_Annotation_AClass');
			$this->assertEqual(Addendum::resolveClassName('AClass'), 'Namespace_Annotation_AClass'); // this is crucial
		}

		public function testClassResolverShouldTriggerErrorOnCommonSuffix() {
			$this->expectError("Cannot resolve class name for 'CommonSuffix'. Possible matches: Namespace1_CommonSuffix, Namespace2_CommonSuffix");
			Addendum::resolveClassName('CommonSuffix');
		}	}
?>
