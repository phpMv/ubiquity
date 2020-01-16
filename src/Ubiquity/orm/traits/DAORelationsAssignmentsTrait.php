<?php

namespace Ubiquity\orm\traits;

use Ubiquity\orm\OrmUtils;
use Ubiquity\log\Logger;
use Ubiquity\orm\parser\Reflexion;
use Ubiquity\orm\parser\ConditionParser;
use Ubiquity\orm\parser\ManyToManyParser;

/**
 * Used by DAO class, realize relations assignments.
 * Ubiquity\orm\traits$DAORelationsAssignmentsTrait
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.2
 *
 */
trait DAORelationsAssignmentsTrait {

	protected static function setToMember($member, $instance, $value, $accessor) {
		$instance->$accessor ( $value );
		$instance->_rest [$member] = $value;
	}

	protected static function getAccessor($member, $instance, $part) {
		$accessor = "set" . ucfirst ( $member );
		if (method_exists ( $instance, $accessor )) {
			return $accessor;
		}
		$class = get_class ( $instance );
		Logger::warn ( "DAO", "Missing modifier " . $accessor . " in " . $class, $part );
		return false;
	}

	public static function _affectsRelationObjects($className, $classPropKey, $manyToOneQueries, $oneToManyQueries, $manyToManyParsers, $objects, $included, $useCache): void {
		if (\sizeof ( $manyToOneQueries ) > 0) {
			self::_affectsObjectsFromArray ( $manyToOneQueries, $included, function ($object, $member, $manyToOneObjects, $fkField, $accessor) {
				self::affectsManyToOneFromArray ( $object, $member, $manyToOneObjects, $fkField, $accessor );
			}, 'getManyToOne' );
		}
		if (\sizeof ( $oneToManyQueries ) > 0) {
			self::_affectsObjectsFromArray ( $oneToManyQueries, $included, function ($object, $member, $relationObjects, $fkField, $accessor, $class, $prop) use ($classPropKey) {
				self::affectsOneToManyFromArray ( $object, $member, $relationObjects, $fkField, $accessor, $class, $prop, $classPropKey );
			}, 'getOneToMany' );
		}
		if (\sizeof ( $manyToManyParsers ) > 0) {
			self::_affectsManyToManyObjectsFromArray ( $className, $manyToManyParsers, $objects, $included, $useCache );
		}
	}

	private static function affectsManyToOneFromArray($object, $member, $manyToOneObjects, $fkField, $accessor) {
		if (isset ( $object->$fkField )) {
			$value = $manyToOneObjects [$object->$fkField];
			self::setToMember ( $member, $object, $value, $accessor );
		}
	}

	/**
	 *
	 * @param object $instance
	 * @param string $member
	 * @param array $array
	 * @param string $mappedByAccessor
	 * @param string $class
	 * @param \ReflectionProperty $prop
	 */
	private static function affectsOneToManyFromArray($instance, $member, $array = null, $mappedByAccessor = null, $accessor = "", $class = "", $prop = null, $classPropKey = null) {
		$ret = [ ];
		self::_getOneToManyFromArray ( $ret, $array, Reflexion::getPropValue ( $instance, $classPropKey ), $mappedByAccessor, $prop );
		self::setToMember ( $member, $instance, $ret, $accessor );
	}

	private static function _affectsObjectsFromArray($queries, $included, $affectsCallback, $part, $useCache = NULL) {
		$includedNext = false;
		foreach ( $queries as $key => $pendingRelationsRequest ) {
			list ( $class, $member, $fkField, $fkClass ) = \explode ( "|", $key );
			if (is_array ( $included )) {
				$includedNext = self::_getIncludedNext ( $included, $member );
			}
			$objectsParsers = $pendingRelationsRequest->getObjectsConditionParsers ();
			$prop = null;
			if ('getOneToMany' === $part) {
				$prop = OrmUtils::getFirstPropKey ( $fkClass );
				$fkField = "get" . ucfirst ( $fkField );
			}
			foreach ( $objectsParsers as $objectsConditionParser ) {
				$objectsConditionParser->compileParts ();
				$relationObjects = self::_getAll ( self::getDb ( $class ), $class, $objectsConditionParser->getConditionParser (), $includedNext, $useCache );
				$objects = $objectsConditionParser->getObjects ();
				if ($accessor = self::getAccessor ( $member, current ( $objects ), $part )) {
					foreach ( $objects as $object ) {
						$affectsCallback ( $object, $member, $relationObjects, $fkField, $accessor, $class, $prop );
					}
				}
			}
		}
	}

	private static function _affectsManyToManyObjectsFromArray($objectsClass, $parsers, $objects, $included, $useCache = NULL) {
		$includedNext = false;
		$prop = OrmUtils::getFirstPropKey ( $objectsClass );
		foreach ( $parsers as $key => $parser ) {
			list ( $class, $member ) = \explode ( "|", $key );
			if (is_array ( $included )) {
				$includedNext = self::_getIncludedNext ( $included, $member );
			}
			$myPkValues = [ ];
			$cParser = self::generateManyToManyParser ( $parser, $myPkValues );
			$relationObjects = self::_getAll ( self::getDb ( $class ), $class, $cParser, $includedNext, $useCache );
			if ($accessor = self::getAccessor ( $member, current ( $objects ), 'getManyToMany' )) {
				foreach ( $objects as $object ) {
					$pkV = Reflexion::getPropValue ( $object, $prop );
					if (isset ( $myPkValues [$pkV] )) {
						$ret = self::getManyToManyFromArrayIds ( $class, $relationObjects, $myPkValues [$pkV] );
						self::setToMember ( $member, $object, $ret, $accessor );
					}
				}
			}
		}
	}

	private static function _getOneToManyFromArray(&$ret, $array, $fkv, $elementAccessor, $prop) {
		foreach ( $array as $element ) {
			$elementRef = $element->$elementAccessor ();
			if (($elementRef == $fkv) || (\is_object ( $elementRef ) && Reflexion::getPropValue ( $elementRef, $prop ) == $fkv)) {
				$ret [] = $element;
			}
		}
	}

	private static function generateManyToManyParser(ManyToManyParser $parser, &$myPkValues): ConditionParser {
		$sql = $parser->generateConcatSQL ();
		$result = self::getDb ( $parser->getTargetEntityClass () )->prepareAndFetchAll ( $sql, $parser->getWhereValues () );
		$condition = $parser->getParserWhereMask ( ' ?' );
		$cParser = new ConditionParser ();
		foreach ( $result as $row ) {
			$values = explode ( ',', $row ['_concat'] );
			$myPkValues [$row ['_field']] = $values;
			$cParser->addParts ( $condition, $values );
		}
		$cParser->compileParts ();
		return $cParser;
	}

	private static function _getIncludedNext($included, $member) {
		return (isset ( $included [$member] )) ? (\is_bool ( $included [$member] ) ? $included [$member] : [ $included [$member] ]) : false;
	}

	private static function getManyToManyFromArrayIds($objectClass, $relationObjects, $ids): array {
		$ret = [ ];
		$prop = OrmUtils::getFirstPropKey ( $objectClass );
		foreach ( $relationObjects as $targetEntityInstance ) {
			$id = Reflexion::getPropValue ( $targetEntityInstance, $prop );
			if (\array_search ( $id, $ids ) !== false) {
				\array_push ( $ret, $targetEntityInstance );
			}
		}
		return $ret;
	}
}

