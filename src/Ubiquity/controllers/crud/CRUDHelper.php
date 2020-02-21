<?php

namespace Ubiquity\controllers\crud;

use Ubiquity\orm\OrmUtils;
use Ubiquity\utils\http\URequest;
use Ubiquity\orm\DAO;
use Ubiquity\orm\parser\Reflexion;
use Ubiquity\cache\database\DbCache;
use Ubiquity\db\SqlUtils;

/**
 * Ubiquity\controllers\crud$CRUDHelper
 * This class is part of Ubiquity
 *
 * @author jc
 * @version 1.0.1
 *
 */
class CRUDHelper {

	public static function getIdentifierFunction($model) {
		$pks = self::getPks ( $model );
		return function ($index, $instance) use ($pks) {
			$values = [ ];
			foreach ( $pks as $pk ) {
				$getter = "get" . ucfirst ( $pk );
				if (method_exists ( $instance, $getter )) {
					$values [] = $instance->{$getter} ();
				}
			}
			return implode ( "_", $values );
		};
	}

	public static function search($model, $search, $fields, $initialCondition = "1=1") {
		$words = preg_split ( "@(\s*?(\(|\)|\|\||\&\&)\s*?)@", $search );
		$params = [ ];
		$count = \count ( $fields );
		$db = DAO::getDb ( $model );
		$like = $db->getSpecificSQL ( 'tostring' ) . ' LIKE ';
		SqlUtils::$quote = $db->quote;
		if ($words !== false) {
			$words = array_filter ( $words, 'strlen' );
			$condition = "(" . SqlUtils::getSearchWhere ( $like, $fields, '?', '', '' ) . ")";
			foreach ( $words as $word ) {
				$word = trim ( $word );
				$params = [ ...$params,...(\array_fill ( 0, $count, "%{$word}%" )) ];
			}

			$condition = str_replace ( "||", " OR ", $condition );
			$condition = str_replace ( "&&", " AND ", $condition );
			$condition = '(' . $condition . ') AND ' . $initialCondition . '';
		} else {
			$condition = $initialCondition;
		}
		return DAO::getAll ( $model, $condition, false, $params );
	}

	public static function update($instance, $values, $setValues = true, $updateMany = true) {
		$className = \get_class ( $instance );
		$fieldsInRelationForUpdate = OrmUtils::getFieldsInRelationsForUpdate_ ( $className );
		$manyToOneRelations = $fieldsInRelationForUpdate ["manyToOne"];
		$manyToManyRelations = $fieldsInRelationForUpdate ["manyToMany"];

		$members = array_keys ( $values );
		OrmUtils::setFieldToMemberNames ( $members, $fieldsInRelationForUpdate ["relations"] );
		$update = false;

		$fieldTypes = OrmUtils::getFieldTypes ( $className );
		foreach ( $fieldTypes as $property => $type ) {
			if ($type == "tinyint(1)") {
				if (isset ( $values [$property] )) {
					$values [$property] = 1;
				} else {
					$values [$property] = 0;
				}
			}
		}
		if ($setValues) {
			URequest::setValuesToObject ( $instance, $values );
		}
		if ($manyToOneRelations) {
			self::updateManyToOne ( $manyToOneRelations, $members, $className, $instance, $values );
		}
		if (isset ( $instance )) {
			if ($instance->_new) {
				$update = DAO::insert ( $instance );
			} else {
				$update = DAO::update ( $instance );
				if (DbCache::$active) {
					// TODO update dbCache
				}
			}
			if ($updateMany && $update && $manyToManyRelations) {
				self::updateManyToMany ( $manyToManyRelations, $members, $className, $instance, $values );
			}
		}
		return $update;
	}

	protected static function updateManyToOne($manyToOneRelations, $members, $className, $instance, $values) {
		foreach ( $manyToOneRelations as $member ) {
			if (array_search ( $member, $members ) !== false) {
				$joinColumn = OrmUtils::getAnnotationInfoMember ( $className, "#joinColumn", $member );
				if ($joinColumn) {
					$fkClass = $joinColumn ["className"];
					$fkField = $joinColumn ["name"];
					if (isset ( $values [$fkField] )) {
						$fkObject = DAO::getById ( $fkClass, $values ["$fkField"] );
						Reflexion::setMemberValue ( $instance, $member, $fkObject );
					}
				}
			}
		}
	}

	protected static function updateManyToMany($manyToManyRelations, $members, $className, $instance, $values) {
		foreach ( $manyToManyRelations as $member ) {
			if (array_search ( $member, $members ) !== false) {
				if (($annot = OrmUtils::getAnnotationInfoMember ( $className, "#manyToMany", $member )) !== false) {
					$newField = $member . "Ids";
					$fkClass = $annot ["targetEntity"];
					$fkObjects = DAO::getAll ( $fkClass, self::getMultiWhere ( $values [$newField], $className ) );
					if (Reflexion::setMemberValue ( $instance, $member, $fkObjects )) {
						DAO::insertOrUpdateManyToMany ( $instance, $member );
					}
				}
			}
		}
	}

	private static function getPks($model) {
		$instance = new $model ();
		return OrmUtils::getKeyFields ( $instance );
	}

	private static function getMultiWhere($ids, $class) {
		$pk = OrmUtils::getFirstKey ( $class );
		$ids = explode ( ",", $ids );
		if (sizeof ( $ids ) < 1)
			return "";
		$strs = [ ];
		$idCount = \sizeof ( $ids );
		for($i = 0; $i < $idCount; $i ++) {
			$strs [] = $pk . "='" . $ids [$i] . "'";
		}
		return implode ( " OR ", $strs );
	}

	public static function getFkIntance($instance, $model, $member, $included = false) {
		$result = [ ];
		if (($annot = OrmUtils::getAnnotationInfoMember ( $model, "#oneToMany", $member )) !== false) {
			$objectFK = DAO::getOneToMany ( $instance, $member, $included );
			$fkClass = $annot ["className"];
		} elseif (($annot = OrmUtils::getAnnotationInfoMember ( $model, "#manyToMany", $member )) !== false) {
			$objectFK = DAO::getManyToMany ( $instance, $member );
			$fkClass = $annot ["targetEntity"];
		} else {
			$objectFK = Reflexion::getMemberValue ( $instance, $member );
			if (! is_object ( $objectFK )) {
				$objectFK = DAO::getManyToOne ( $instance, $member, $included );
			}
			if (isset ( $objectFK ))
				$fkClass = \get_class ( $objectFK );
		}
		if (isset ( $fkClass )) {
			$fkTable = OrmUtils::getTableName ( $fkClass );
			$result [$member] = compact ( "objectFK", "fkClass", "fkTable" );
		}
		return $result;
	}

	public static function getFKIntances($instance, $model, $included = false) {
		$result = [ ];
		$relations = OrmUtils::getFieldsInRelations ( $model );
		foreach ( $relations as $member ) {
			$fkInstance = self::getFkIntance ( $instance, $model, $member, $included );
			if (sizeof ( $fkInstance ) > 0) {
				$result = array_merge ( $result, $fkInstance );
			}
		}
		return $result;
	}
}

