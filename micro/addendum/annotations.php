<?php
	/**
	 * Addendum PHP Reflection Annotations
	 * http://code.google.com/p/addendum/
	 *
	 * Copyright (C) 2006-2009 Jan "johno Suchal <johno@jsmf.net>

	 * This library is free software; you can redistribute it and/or
	 * modify it under the terms of the GNU Lesser General Public
	 * License as published by the Free Software Foundation; either
	 * version 2.1 of the License, or (at your option) any later version.

	 * This library is distributed in the hope that it will be useful,
	 * but WITHOUT ANY WARRANTY; without even the implied warranty of
	 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
	 * Lesser General Public License for more details.

	 * You should have received a copy of the GNU Lesser General Public
	 * License along with this library; if not, write to the Free Software
	 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
	 **/

	require_once(dirname(__FILE__).'/annotations/annotation_parser.php');

	class Annotation {
		public $value;
		private static $creationStack = array();

		public final function __construct($data = array(), $target = false) {
			$reflection = new ReflectionClass($this);
			$class = $reflection->getName();
			if(isset(self::$creationStack[$class])) {
				trigger_error("Circular annotation reference on '$class'", E_USER_ERROR);
				return;
			}
			self::$creationStack[$class] = true;
			foreach($data as $key => $value) {
				if($reflection->hasProperty($key)) {
					$this->$key = $value;
				} else {
					trigger_error("Property '$key' not defined for annotation '$class'");
				}
			}
			$this->checkTargetConstraints($target);
			$this->checkConstraints($target);
			unset(self::$creationStack[$class]);
		}

		private function checkTargetConstraints($target) {
			$reflection = new ReflectionAnnotatedClass($this);
			if($reflection->hasAnnotation('Target')) {
				$value = $reflection->getAnnotation('Target')->value;
				$values = is_array($value) ? $value : array($value);
				foreach($values as $value) {
					if($value == 'class' && $target instanceof ReflectionClass) return;
					if($value == 'method' && $target instanceof ReflectionMethod) return;
					if($value == 'property' && $target instanceof ReflectionProperty) return;
					if($value == 'nested' && $target === false) return;
				}
				if($target === false) {
					trigger_error("Annotation '".get_class($this)."' nesting not allowed", E_USER_ERROR);
				} else {
					trigger_error("Annotation '".get_class($this)."' not allowed on ".$this->createName($target), E_USER_ERROR);
				}
			}
		}

		private function createName($target) {
			if($target instanceof ReflectionMethod) {
				return $target->getDeclaringClass()->getName().'::'.$target->getName();
			} elseif($target instanceof ReflectionProperty) {
				return $target->getDeclaringClass()->getName().'::$'.$target->getName();
			} else {
				return $target->getName();
			}
		}

		protected function checkConstraints($target) {}
	}

	class AnnotationsCollection {
		private $annotations;

		public function __construct($annotations) {
			$this->annotations = $annotations;
		}

		public function hasAnnotation($class) {
			$class = Addendum::resolveClassName($class);
			return isset($this->annotations[$class]);
		}

		public function getAnnotation($class) {
			$class = Addendum::resolveClassName($class);
			return isset($this->annotations[$class]) ? end($this->annotations[$class]) : false;
		}

		public function getAnnotations() {
			$result = array();
			foreach($this->annotations as $instances) {
				$result[] = end($instances);
			}
			return $result;
		}

		public function getAllAnnotations($restriction = false) {
			$restriction = Addendum::resolveClassName($restriction);
			$result = array();
			foreach($this->annotations as $class => $instances) {
				if(!$restriction || $restriction == $class) {
					$result = array_merge($result, $instances);
				}
			}
			return $result;
		}
	}

	class Annotation_Target extends Annotation {}

	class AnnotationsBuilder {
		private static $cache = array();

		public function build($targetReflection) {
			$data = $this->parse($targetReflection);
			$annotations = array();
			foreach($data as $class => $parameters) {
				foreach($parameters as $params) {
					$annotation = $this->instantiateAnnotation($class, $params, $targetReflection);
					if($annotation !== false) {
						$annotations[get_class($annotation)][] = $annotation;
					}
				}
			}
			return new AnnotationsCollection($annotations);
		}

		public function instantiateAnnotation($class, $parameters, $targetReflection = false) {
			$class = Addendum::resolveClassName($class);
			if(is_subclass_of($class, 'Annotation') && !Addendum::ignores($class) || $class == 'Annotation') {
				$annotationReflection = new ReflectionClass($class);
				return $annotationReflection->newInstance($parameters, $targetReflection);
			}
			return false;
		}

		private function parse($reflection) {
			$key = $this->createName($reflection);
			if(!isset(self::$cache[$key])) {
				$parser = new AnnotationsMatcher;
				$parser->matches($this->getDocComment($reflection), $data);
				self::$cache[$key] = $data;
			}
			return self::$cache[$key];
		}

		private function createName($target) {
			if($target instanceof ReflectionMethod) {
				return $target->getDeclaringClass()->getName().'::'.$target->getName();
			} elseif($target instanceof ReflectionProperty) {
				return $target->getDeclaringClass()->getName().'::$'.$target->getName();
			} else {
				return $target->getName();
			}
		}

		protected function getDocComment($reflection) {
			return Addendum::getDocComment($reflection);
		}

		public static function clearCache() {
			self::$cache = array();
		}
	}

	class ReflectionAnnotatedClass extends ReflectionClass {
		private $annotations;

		public function __construct($class) {
			parent::__construct($class);
			$this->annotations = $this->createAnnotationBuilder()->build($this);
		}

		public function hasAnnotation($class) {
			return $this->annotations->hasAnnotation($class);
		}

		public function getAnnotation($annotation) {
			return $this->annotations->getAnnotation($annotation);
		}

		public function getAnnotations() {
			return $this->annotations->getAnnotations();
		}

		public function getAllAnnotations($restriction = false) {
			return $this->annotations->getAllAnnotations($restriction);
		}

		public function getConstructor() {
			return $this->createReflectionAnnotatedMethod(parent::getConstructor());
		}

		public function getMethod($name) {
			return $this->createReflectionAnnotatedMethod(parent::getMethod($name));
		}

		public function getMethods($filter = -1) {
			$result = array();
			foreach(parent::getMethods($filter) as $method) {
				$result[] = $this->createReflectionAnnotatedMethod($method);
			}
			return $result;
		}

		public function getProperty($name) {
			return $this->createReflectionAnnotatedProperty(parent::getProperty($name));
		}

		public function getProperties($filter = -1) {
			$result = array();
			foreach(parent::getProperties($filter) as $property) {
				$result[] = $this->createReflectionAnnotatedProperty($property);
			}
			return $result;
		}

		public function getInterfaces() {
			$result = array();
			foreach(parent::getInterfaces() as $interface) {
				$result[] = $this->createReflectionAnnotatedClass($interface);
			}
			return $result;
		}

		public function getParentClass() {
			$class = parent::getParentClass();
			return $this->createReflectionAnnotatedClass($class);
		}

		protected function createAnnotationBuilder() {
			return new AnnotationsBuilder();
		}

		private function createReflectionAnnotatedClass($class) {
			return ($class !== false) ? new ReflectionAnnotatedClass($class->getName()) : false;
		}

		private function createReflectionAnnotatedMethod($method) {
			return ($method !== null) ? new ReflectionAnnotatedMethod($this->getName(), $method->getName()) : null;
		}

		private function createReflectionAnnotatedProperty($property) {
			return ($property !== null) ? new ReflectionAnnotatedProperty($this->getName(), $property->getName()) : null;
		}
	}

	class ReflectionAnnotatedMethod extends ReflectionMethod {
		private $annotations;

		public function __construct($class, $name) {
			parent::__construct($class, $name);
			$this->annotations = $this->createAnnotationBuilder()->build($this);
		}

		public function hasAnnotation($class) {
			return $this->annotations->hasAnnotation($class);
		}

		public function getAnnotation($annotation) {
			return $this->annotations->getAnnotation($annotation);
		}

		public function getAnnotations() {
			return $this->annotations->getAnnotations();
		}

		public function getAllAnnotations($restriction = false) {
			return $this->annotations->getAllAnnotations($restriction);
		}

		public function getDeclaringClass() {
			$class = parent::getDeclaringClass();
			return new ReflectionAnnotatedClass($class->getName());
		}

		protected function createAnnotationBuilder() {
			return new AnnotationsBuilder();
		}
	}

	class ReflectionAnnotatedProperty extends ReflectionProperty {
		private $annotations;

		public function __construct($class, $name) {
			parent::__construct($class, $name);
			$this->annotations = $this->createAnnotationBuilder()->build($this);
		}

		public function hasAnnotation($class) {
			return $this->annotations->hasAnnotation($class);
		}

		public function getAnnotation($annotation) {
			return $this->annotations->getAnnotation($annotation);
		}

		public function getAnnotations() {
			return $this->annotations->getAnnotations();
		}

		public function getAllAnnotations($restriction = false) {
			return $this->annotations->getAllAnnotations($restriction);
		}

		public function getDeclaringClass() {
			$class = parent::getDeclaringClass();
			return new ReflectionAnnotatedClass($class->getName());
		}

		protected function createAnnotationBuilder() {
			return new AnnotationsBuilder();
		}
	}

	class Addendum {
		private static $rawMode;
		private static $ignore;
		private static $classnames = array();
		private static $annotations = false;

		public static function getDocComment($reflection) {
			if(self::checkRawDocCommentParsingNeeded()) {
				$docComment = new DocComment();
				return $docComment->get($reflection);
			} else {
				return $reflection->getDocComment();
			}
		}

		/** Raw mode test */
		private static function checkRawDocCommentParsingNeeded() {
			if(self::$rawMode === null) {
				$reflection = new ReflectionClass('Addendum');
				$method = $reflection->getMethod('checkRawDocCommentParsingNeeded');
				self::setRawMode($method->getDocComment() === false);
			}
			return self::$rawMode;
		}

		public static function setRawMode($enabled = true) {
			if($enabled) {
				require_once(dirname(__FILE__).'/annotations/doc_comment.php');
			}
			self::$rawMode = $enabled;
		}

		public static function resetIgnoredAnnotations() {
			self::$ignore = array();
		}

		public static function ignores($class) {
			return isset(self::$ignore[$class]);
		}

		public static function ignore() {
			foreach(func_get_args() as $class) {
				self::$ignore[$class] = true;
			}
		}

		public static function resolveClassName($class) {
			if(isset(self::$classnames[$class])) return self::$classnames[$class];
			$matching = array();
			foreach(self::getDeclaredAnnotations() as $declared) {
				if($declared == $class) {
					$matching[] = $declared;
				} else {
					$pos = strrpos($declared, "_$class");
					if($pos !== false && ($pos + strlen($class) == strlen($declared) - 1)) {
						$matching[] = $declared;
					}
				}
			}
			$result = null;
			switch(count($matching)) {
				case 0: $result = $class; break;
				case 1: $result = $matching[0]; break;
				default: trigger_error("Cannot resolve class name for '$class'. Possible matches: " . join(', ', $matching), E_USER_ERROR);
			}
			self::$classnames[$class] = $result;
			return $result;
		}

		private static function getDeclaredAnnotations() {
			if(!self::$annotations) {
				self::$annotations = array();
				foreach(get_declared_classes() as $class) {
					if(is_subclass_of($class, 'Annotation') || $class == 'Annotation') self::$annotations[] = $class;
				}
			}
			return self::$annotations;
		}


	}
