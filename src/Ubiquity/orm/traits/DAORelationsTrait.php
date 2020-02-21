<?php

/**
 * Ubiquity\orm\traits
 * This class is part of Ubiquity
 * @author jc
 * @version 1.0.1
 *
 */
namespace Ubiquity\orm\traits;

use Ubiquity\db\Database;
use Ubiquity\log\Logger;
use Ubiquity\orm\OrmUtils;
use Ubiquity\orm\parser\ConditionParser;
use Ubiquity\orm\parser\ManyToManyParser;
use Ubiquity\orm\parser\Reflexion;

trait DAORelationsTrait {

	abstract protected static function _getAll(Database $db, $className, ConditionParser $conditionParser, $included = true, $useCache = NULL);

	public static function _getIncludedForStep($included) {
		if (\is_bool ( $included )) {
			return $included;
		}
		$ret = [ ];
		if (\is_array ( $included )) {
			foreach ( $included as &$includedMember ) {
				if (\is_array ( $includedMember )) {
					foreach ( $includedMember as $iMember ) {
						self::parseEncludeMember ( $ret, $iMember );
					}
				} else {
					self::parseEncludeMember ( $ret, $includedMember );
				}
			}
		}
		return $ret;
	}

	private static function parseEncludeMember(&$ret, $includedMember): void {
		$array = \explode ( '.', $includedMember );
		$member = \array_shift ( $array );
		if (\sizeof ( $array ) > 0) {
			$newValue = \implode ( '.', $array );
			if ($newValue === '*') {
				$newValue = true;
			}
			if (isset ( $ret [$member] )) {
				if (! \is_array ( $ret [$member] )) {
					$ret [$member] = [ $ret [$member] ];
				}
				$ret [$member] [] = $newValue;
			} else {
				$ret [$member] = $newValue;
			}
		} else {
			if (isset ( $member ) && '' != $member) {
				$ret [$member] = false;
			} else {
				return;
			}
		}
	}

	private static function getInvertedJoinColumns($included, &$invertedJoinColumns): void {
		foreach ( $invertedJoinColumns as $column => &$annot ) {
			$member = $annot ['member'];
			if (isset ( $included [$member] ) === false) {
				unset ( $invertedJoinColumns [$column] );
			}
		}
	}

	private static function getToManyFields($included, &$toManyFields): void {
		foreach ( $toManyFields as $member => $annotNotUsed ) {
			if (isset ( $included [$member] ) === false) {
				unset ( $toManyFields [$member] );
			}
		}
	}

	public static function _initRelationFields($included, $metaDatas, &$invertedJoinColumns, &$oneToManyFields, &$manyToManyFields): void {
		if (isset ( $metaDatas ['#invertedJoinColumn'] )) {
			$invertedJoinColumns = $metaDatas ['#invertedJoinColumn'];
		}
		if (isset ( $metaDatas ['#oneToMany'] )) {
			$oneToManyFields = $metaDatas ['#oneToMany'];
		}
		if (isset ( $metaDatas ['#manyToMany'] )) {
			$manyToManyFields = $metaDatas ['#manyToMany'];
		}
		if (\is_array ( $included )) {
			if (isset ( $invertedJoinColumns )) {
				self::getInvertedJoinColumns ( $included, $invertedJoinColumns );
			}
			if (isset ( $oneToManyFields )) {
				self::getToManyFields ( $included, $oneToManyFields );
			}
			if (isset ( $manyToManyFields )) {
				self::getToManyFields ( $included, $manyToManyFields );
			}
		}
	}

	private static function getManyToManyFromArray($instance, $array, $class, $parser): array {
		$ret = [ ];
		$continue = true;
		$accessorToMember = 'get' . \ucfirst ( $parser->getInversedBy () );
		$myPkAccessor = 'get' . \ucfirst ( $parser->getMyPk () );
		$pk = self::getFirstKeyValue_ ( $instance );

		if (sizeof ( $array ) > 0) {
			$continue = \method_exists ( current ( $array ), $accessorToMember );
		}
		if ($continue) {
			foreach ( $array as $targetEntityInstance ) {
				$instances = $targetEntityInstance->$accessorToMember ();
				if (is_array ( $instances )) {
					foreach ( $instances as $inst ) {
						if ($inst->$myPkAccessor () == $pk)
							\array_push ( $ret, $targetEntityInstance );
					}
				}
			}
		} else {
			Logger::warn ( 'DAO', "L'accesseur au membre " . $parser->getInversedBy () . ' est manquant pour ' . $parser->getTargetEntity (), 'ManyToMany' );
		}
		return $ret;
	}

	/**
	 * Loads member associated with $instance by a ManyToOne relationship
	 *
	 * @param object|array $instance The instance object or an array with [classname,id]
	 * @param string $member The member to load
	 * @param boolean|array $included if true, loads associate members with associations, if array, example : ["client.*","commands"]
	 * @param boolean|null $useCache
	 */
	public static function getManyToOne($instance, $member, $included = false, $useCache = NULL): ?object {
		$classname = self::getClass_ ( $instance );
		if (\is_array ( $instance )) {
			$instance = self::getById ( $classname, $instance [1], false, $useCache );
		}
		$fieldAnnot = OrmUtils::getMemberJoinColumns ( $classname, $member );
		if ($fieldAnnot !== null) {
			$annotationArray = $fieldAnnot [1];
			$member = $annotationArray ['member'];
			$value = Reflexion::getMemberValue ( $instance, $member );
			$key = OrmUtils::getFirstKey ( $annotationArray ['className'] );
			$kv = array ($key => $value );
			$obj = self::getById ( $annotationArray ['className'], $kv, $included, $useCache );
			if ($obj !== null) {
				Logger::info ( 'DAO', 'Loading the member ' . $member . ' for the object ' . $classname, 'getManyToOne' );
				$accesseur = 'set' . ucfirst ( $member );
				if (\is_object ( $instance ) && \method_exists ( $instance, $accesseur )) {
					$instance->$accesseur ( $obj );
					$instance->_rest [$member] = $obj->_rest;
				}
				return $obj;
			}
		}
		return null;
	}

	/**
	 * Assign / load the child records in the $member member of $instance.
	 *
	 * @param object|array $instance The instance object or an array with [classname,id]
	 * @param string $member Member on which a oneToMany annotation must be present
	 * @param boolean|array $included if true, loads associate members with associations, if array, example : ['client.*','commands']
	 * @param boolean $useCache
	 * @param array $annot used internally
	 */
	public static function getOneToMany($instance, $member, $included = true, $useCache = NULL, $annot = null): array {
		$ret = array ();
		$class = self::getClass_ ( $instance );
		if (! isset ( $annot )) {
			$annot = OrmUtils::getAnnotationInfoMember ( $class, '#oneToMany', $member );
		}
		if ($annot !== false) {
			$fkAnnot = OrmUtils::getAnnotationInfoMember ( $annot ['className'], '#joinColumn', $annot ['mappedBy'] );
			if ($fkAnnot !== false) {
				$fkv = self::getFirstKeyValue_ ( $instance );
				$db = self::getDb ( $annot ['className'] );
				$ret = self::_getAll ( $db, $annot ['className'], ConditionParser::simple ( $db->quote . $fkAnnot ['name'] . $db->quote . '= ?', $fkv ), $included, $useCache );
				if (is_object ( $instance ) && $modifier = self::getAccessor ( $member, $instance, 'getOneToMany' )) {
					self::setToMember ( $member, $instance, $ret, $modifier );
				}
			}
		}
		return $ret;
	}

	/**
	 * Assigns / loads the child records in the $member member of $instance.
	 * If $array is null, the records are loaded from the database
	 *
	 * @param object|array $instance The instance object or an array with [classname,id]
	 * @param string $member Member on which a ManyToMany annotation must be present
	 * @param boolean|array $included if true, loads associate members with associations, if array, example : ['client.*','commands']
	 * @param array $array optional parameter containing the list of possible child records
	 * @param boolean $useCache
	 */
	public static function getManyToMany($instance, $member, $included = false, $array = null, $useCache = NULL): array {
		$ret = [ ];
		$class = self::getClass_ ( $instance );
		$db = self::getDb ( $class );
		$parser = new ManyToManyParser ( $db, $class, $member );
		if ($parser->init ()) {
			if (\is_null ( $array )) {
				$targetEntityClass = $parser->getTargetEntityClass ();
				$pk = self::getFirstKeyValue_ ( $instance );
				if ($pk != null) {
					$quote = $db->quote;
					$condition = ' INNER JOIN ' . $quote . $parser->getJoinTable () . $quote . ' on ' . $quote . $parser->getJoinTable () . $quote . '.' . $quote . $parser->getFkField () . $quote . '=' . $quote . $parser->getTargetEntityTable () . $quote . '.' . $quote . $parser->getPk () . $quote . ' WHERE ' . $quote . $parser->getJoinTable () . $quote . '.' . $quote . $parser->getMyFkField () . $quote . '= ?';
					$ret = self::_getAll ( $db, $targetEntityClass, ConditionParser::simple ( $condition, $pk ), $included, $useCache );
				}
			} else {
				$ret = self::getManyToManyFromArray ( $instance, $array, $class, $parser );
			}
			if (\is_object ( $instance ) && $modifier = self::getAccessor ( $member, $instance, 'getManyToMany' )) {
				self::setToMember ( $member, $instance, $ret, $modifier );
			}
		}
		return $ret;
	}

	/**
	 *
	 * @param object $instance
	 * @param array $array
	 * @param boolean $useCache
	 */
	public static function affectsManyToManys($instance, $array = NULL, $useCache = NULL) {
		$metaDatas = OrmUtils::getModelMetadata ( \get_class ( $instance ) );
		$manyToManyFields = $metaDatas ['#manyToMany'];
		if (\sizeof ( $manyToManyFields ) > 0) {
			foreach ( $manyToManyFields as $member ) {
				self::getManyToMany ( $instance, $member, false, $array, $useCache );
			}
		}
	}
}
