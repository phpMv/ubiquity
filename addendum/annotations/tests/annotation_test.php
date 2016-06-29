<?php
	require_once('simpletest/autorun.php');
	require_once(dirname(__FILE__).'/../../annotations.php');
	
	class TestingAnnotation extends Annotation {
		public $optional = 'default';
		public $required;
	}

	class Annotation1 extends Annotation {}
	class Annotation2 extends Annotation {}
	class Namespace_Annotation3 extends Annotation {}
	
	class TestOfAnnotation extends UnitTestCase {
		public function testConstructorShouldNotFailOnNoValues() {
			$annotation = new TestingAnnotation;
		}

		public function testConstructorShouldNotFailOnNoTarget() {
			$annotation = new TestingAnnotation(array());
		}
	
		public function testConstructorsFillsParameters() {
			$annotation = new TestingAnnotation(array('optional' => 1, 'required' => 2));
			$this->assertEqual($annotation->optional, 1);
			$this->assertEqual($annotation->required, 2);
		}
		
		public function testConstructorThrowsErrorOnInvalidParameter() {
			$this->expectError("Property 'unknown' not defined for annotation 'TestingAnnotation'");
			$annotation = new TestingAnnotation(array('unknown' => 1), $this);
		}
		
		public function TODO_testConstructorThrowsErrorWithoutSpecifingRequiredParameters() {
			$this->expectError("Property 'required' in annotation 'TestingAnnotation' is required");
			$annotation = new TestingAnnotation();
		}
	}

	class TestOfAnnotationCollection extends UnitTestCase {
		private $annotations;

		public function setUp() {
			$this->annotations = new AnnotationsCollection(array(
				'Annotation1' => array(new Annotation1(array('value' => false)), new Annotation1(array('value' => true))), 
				'Annotation2' => array(new Annotation2),
				'Namespace_Annotation3' => array(new Namespace_Annotation3)
			));
		}

		public function testHasAnnotation() {
			$this->assertTrue($this->annotations->hasAnnotation('Annotation1'));
			$this->assertFalse($this->annotations->hasAnnotation('Bad'));
			$this->assertTrue($this->annotations->hasAnnotation('Annotation3'));
		}

		public function testGetAnnotation() {
			$this->assertIsA($this->annotations->getAnnotation('Annotation3'), 'Namespace_Annotation3');
			$this->assertFalse($this->annotations->getAnnotation('Bad'));
		}

		public function testGetAnnotations() {
			$annotations = $this->annotations->getAnnotations();
			$this->assertEqual(count($annotations), 3);
			$this->assertTrue($annotations[0]->value);
		}

		public function testGetAllAnnotations() {
			$this->assertEqual(count($this->annotations->getAllAnnotations()), 4);
			$this->assertEqual(count($this->annotations->getAllAnnotations('Annotation1')), 2);
			$this->assertEqual(count($this->annotations->getAllAnnotations('Annotation3')), 1);
			$this->assertEqual(count($this->annotations->getAllAnnotations('Bad')), 0);
		}
	}
?>
