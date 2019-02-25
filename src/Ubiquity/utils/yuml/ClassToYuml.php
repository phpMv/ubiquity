<?php

namespace Ubiquity\utils\yuml;

use Ubiquity\orm\OrmUtils;
use Ubiquity\utils\yuml\traits\ClassToYumlRelationsTrait;

/**
 * yuml export tool for class
 * Ubiquity\utils\yuml$ClassToYuml
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.3
 *
 */
class ClassToYuml {
	use ClassToYumlRelationsTrait;
	protected $class;
	protected $displayProperties = true;
	protected $displayMethods = false;
	protected $displayMethodsParams = false;
	protected $displayPropertiesTypes = false;
	protected $displayAssociations;
	protected $displayAssociationClassProperties = false;
	protected $displayForeignKeys = true;
	protected $properties;
	protected $parseResult;
	protected $note;

	public function __construct($class, $displayProperties = true, $displayAssociations = true, $displayMethods = false, $displayMethodsParams = false, $displayPropertiesTypes = false, $displayAssociationClassProperties = false) {
		$this->class = $class;
		$this->displayProperties = $displayProperties;
		$this->displayAssociations = $displayAssociations;
		$this->displayMethods = $displayMethods;
		$this->displayMethodsParams = $displayMethodsParams;
		$this->displayPropertiesTypes = $displayPropertiesTypes;
		$this->displayAssociationClassProperties = $displayAssociationClassProperties;
	}

	public function init($hasManyToOne, $hasOneToMany, $hasManyToMany) {
		if ($hasManyToOne) {
			$this->loadManyToOne ();
		}
		if ($hasOneToMany) {
			$this->loadOneToManys ();
		}
		if ($hasManyToMany) {
			$this->loadManyToManys ();
		}
	}

	public function parse() {
		$reflect = new \ReflectionClass ( $this->class );
		$yumlAnnot = OrmUtils::getAnnotationInfo ( $this->class, "#yuml" );
		$color = "";
		if ($yumlAnnot !== false) {
			if (isset ( $yumlAnnot ["color"] )) {
				$color = "{bg:" . $yumlAnnot ["color"] . "}";
			}
			if (isset ( $yumlAnnot ["note"] )) {
				$this->note = $yumlAnnot ["note"];
			}
		}
		$parts = [ $reflect->getShortName () ];

		if ($this->displayProperties) {
			$prikeys = OrmUtils::getKeyFields ( $this->class );
			$types = OrmUtils::getFieldTypes ( $this->class );
			$propertiesArray = [ ];
			$properties = $reflect->getProperties ();
			foreach ( $properties as $property ) {
				$this->parseProperty ( $propertiesArray, $property, $prikeys, $types );
			}
			$parts [] = \implode ( Yuml::$memberSeparator, $propertiesArray );
		}

		if ($this->displayMethods) {
			$methodsArray = [ ];
			$methods = $reflect->getMethods ();
			foreach ( $methods as $method ) {
				$this->parseMethod ( $methodsArray, $method );
			}
			$parts [] = \implode ( Yuml::$memberSeparator, $methodsArray );
		}

		$result = \implode ( Yuml::$classSeparator, $parts ) . $color;
		$result = Yuml::setClassContent ( $result );
		if (isset ( $this->note )) {
			$result .= $this->_getNote ();
		}
		$this->parseResult = $result;
		return $result;
	}

	protected function parseProperty(&$propertiesArray, $property, $prikeys, $types) {
		$propertyName = $property->getName ();
		$type = "";
		$isPri = "";
		if ($this->displayPropertiesTypes) {
			if (\array_key_exists ( $propertyName, $types )) {
				$type = Yuml::$parameterTypeSeparator . $types [$propertyName];
			}
		}
		if (\array_search ( $propertyName, $prikeys ) !== false) {
			$isPri = Yuml::$primary;
		}
		$propertiesArray [] = Yuml::setPropertyVariables ( [ $this->getAccess ( $property ),$isPri,$propertyName,$type ] );
	}

	protected function parseMethod(&$methodsArray, $method) {
		$parameters = "";
		if ($this->displayMethodsParams) {
			$parameters = $this->getMethodParameters ( $method );
		}
		$methodName = $method->getName ();
		$type = "";
		if ($method->hasReturnType ()) {
			$type = Yuml::$parameterTypeSeparator . $method->getReturnType ();
		}
		$methodsArray [] = Yuml::setMethodVariables ( [ $this->getAccess ( $method ),$methodName,$parameters,$type ] );
	}

	protected function _getNote() {
		return "-[note:" . $this->note . "]";
	}

	protected function getMethodParameters(\ReflectionMethod $method) {
		$paramsValues = [ ];
		$parameters = $method->getParameters ();
		foreach ( $parameters as $parameter ) {
			$v = $parameter->getName ();
			if ($parameter->hasType ()) {
				$v .= Yuml::$parameterTypeSeparator . $parameter->getType ();
			}
			$paramsValues [] = $v;
		}
		return \implode ( Yuml::$parameterSeparator, $paramsValues );
	}

	protected function getAccess($property) {
		$result = Yuml::$private;
		if ($property->isPublic ()) {
			$result = Yuml::$public;
		} elseif ($property->isProtected ()) {
			$result = Yuml::$protected;
		}
		return $result;
	}

	public function manyToOneTostring() {
		$this->loadManyToOne ();
		return \implode ( Yuml::$groupeSeparator, $this->_getYumlManyToOne () );
	}

	public function oneToManyTostring() {
		$this->loadOneToManys ();
		return \implode ( Yuml::$groupeSeparator, $this->_getYumlOneToMany () );
	}

	public function manyToManyTostring($load = true) {
		if ($load) {
			$this->loadManyToManys ();
		}
		return \implode ( Yuml::$groupeSeparator, $this->_getYumlManyToMany () );
	}

	public function __toString() {
		$result = [ $this->parse () ];
		if ($this->displayAssociations) {
			$result = \array_merge ( $result, $this->_getYumlManyToOne () );
			$result = \array_merge ( $result, $this->_getYumlOneToMany () );
			$result = \array_merge ( $result, $this->_getYumlManyToMany () );
		}
		return \implode ( Yuml::$groupeSeparator, $result );
	}

	public function setDisplayProperties($displayProperties) {
		$this->displayProperties = $displayProperties;
		return $this;
	}

	public function setDisplayMethods($displayMethods) {
		$this->displayMethods = $displayMethods;
		return $this;
	}

	public function setDisplayAssociations($displayAssociations) {
		$this->displayAssociations = $displayAssociations;
		return $this;
	}
}
