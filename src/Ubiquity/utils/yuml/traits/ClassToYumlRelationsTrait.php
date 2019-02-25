<?php

namespace Ubiquity\utils\yuml\traits;

use Ubiquity\orm\OrmUtils;
use Ubiquity\utils\yuml\ClassToYuml;
use Ubiquity\utils\yuml\Yuml;

/**
 * Ubiquity\utils\yuml\traits$ClassToYumlRelationsTrait
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.3
 *
 */
trait ClassToYumlRelationsTrait {
	protected $oneToManys = [ ];
	protected $manyToOne = [ ];
	protected $manyToManys = [ ];
	protected $extManyToManys = [ ];

	protected function loadOneToManys() {
		$oneToManys = OrmUtils::getAnnotationInfo ( $this->class, "#oneToMany" );
		if ($oneToManys) {
			foreach ( $oneToManys as $member => $array ) {
				$this->oneToManys [$member] = $array ["className"];
			}
		}
	}

	public function loadManyToManys() {
		$manyToManys = OrmUtils::getAnnotationInfo ( $this->class, "#manyToMany" );
		if ($manyToManys) {
			foreach ( $manyToManys as $member => $array ) {
				if (isset ( $array ["targetEntity"] )) {
					$this->manyToManys [$member] = $array ["targetEntity"];
					$this->extManyToManys [$array ["targetEntity"]] = $this->class;
				}
			}
		}
	}

	protected function loadManyToOne() {
		$manyToOne = OrmUtils::getAnnotationInfo ( $this->class, "#manyToOne" );
		if ($manyToOne) {
			foreach ( $manyToOne as $member ) {
				$joinColumn = OrmUtils::getAnnotationInfoMember ( $this->class, "#joinColumn", $member );
				if ($joinColumn && isset ( $joinColumn ["className"] )) {
					$this->manyToOne [$member] = $joinColumn ["className"];
				}
			}
		}
	}

	protected function getShortClassName($class) {
		$reflect = new \ReflectionClass ( $class );
		return $reflect->getShortName ();
	}

	protected function _getYumlRelationsType($relations, $branche) {
		$myClass = $this->getShortClassName ( $this->class );
		$yumlRelations = [ ];
		foreach ( $relations as $model ) {
			$yumlRelations [] = Yuml::setClassContent ( $myClass ) . $branche . new ClassToYuml ( $model, $this->displayAssociationClassProperties, false );
		}
		return $yumlRelations;
	}

	protected function _getYumlManyToOne() {
		return $this->_getYumlRelationsType ( $this->manyToOne, "0..*-1" );
	}

	protected function _getYumlOneToMany() {
		return $this->_getYumlRelationsType ( $this->oneToManys, "1-0..*" );
	}

	protected function _getYumlManyToMany() {
		return $this->_getYumlRelationsType ( $this->manyToManys, "0..*-0..*" );
	}

	/**
	 *
	 * @return array
	 */
	public function getExtManyToManys() {
		return $this->extManyToManys;
	}

	public function removeManyToManyExt($targetClass) {
		$member = array_search ( $targetClass, $this->manyToManys );
		if ($member !== false) {
			unset ( $this->manyToManys [$member] );
		}
	}
}

