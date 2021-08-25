<?php

namespace Ubiquity\controllers\crud;

use Ubiquity\orm\OrmUtils;
use Ubiquity\utils\http\URequest;
use Ubiquity\orm\DAO;
use Ubiquity\orm\parser\Reflexion;
use Ubiquity\cache\database\DbCache;
use Ubiquity\db\SqlUtils;
use Ubiquity\db\utils\DbTypes;

/**
 * Ubiquity\controllers\crud$CRUDHelper
 * This class is part of Ubiquity
 *
 * @author jc
 * @version 1.0.4
 *
 */
class CRUDHelper {

	public static function getIdentifierFunction($model) {
		$pks = OrmUtils::getKeyMembers ( $model );
		return function ($index, $instance) use ($pks) {
			$values = [ ];
			foreach ( $pks as $pk ) {
				$getter = 'get' . \ucfirst ( $pk );
				if (\method_exists ( $instance, $getter )) {
					$values [] = $instance->{$getter} ();
				}
			}
			return \implode ( '_', $values );
		};
	}

	public static function search($model, $search, $fields, $initialCondition = '1=1') {
		$words = \preg_split ( "@(\s*?(\(|\)|\|\||\&\&)\s*?)@", $search );
		$params = [ ];
		$count = \count ( $fields );
		$db = DAO::getDb ( $model );
		$like = $db->getSpecificSQL ( 'tostring' ) . ' LIKE ';
		SqlUtils::$quote = $db->quote;
		if ($words !== false) {
			$words = array_filter ( $words, 'strlen' );
			$condition = "(" . SqlUtils::getSearchWhere ( $like, $fields, '?', '', '' ) . ")";
			foreach ( $words as $word ) {
				$word = \trim ( $word );
				$params = [ ...$params,...(\array_fill ( 0, $count, "%{$word}%" )) ];
			}

			$condition = \str_replace ( '||', ' OR ', $condition );
			$condition = \str_replace ( '&&', ' AND ', $condition );
			$condition = '(' . $condition . ') AND ' . $initialCondition . '';
		} else {
			$condition = $initialCondition;
		}
		return DAO::getAll ( $model, $condition, false, $params );
	}

	public static function update($instance, $values, $setValues = true, $updateMany = true, $eventCallback = null) {
		$className = \get_class ( $instance );
		$fieldsInRelationForUpdate = OrmUtils::getFieldsInRelationsForUpdate_ ( $className );
		$manyToOneRelations = $fieldsInRelationForUpdate ['manyToOne'];
		$manyToManyRelations = $fieldsInRelationForUpdate ['manyToMany'];
		$oneToManyRelations = $fieldsInRelationForUpdate ['oneToMany'];

		$members = \array_keys ( $values );
		OrmUtils::setFieldToMemberNames ( $members, $fieldsInRelationForUpdate ['relations'] );
		$update = false;
		
		self::setInputValues($className, $instance, $values, $setValues);

		if ($manyToOneRelations) {
			self::updateManyToOne ( $manyToOneRelations, $members, $className, $instance, $values );
		}
		if (isset ( $instance )) {
			if (isset ( $eventCallback )) {
				$eventCallback ( $instance, $instance->_new );
			}
			if ($instance->_new) {
				$update = DAO::insert ( $instance );
			} else {
				$update = DAO::update ( $instance );
				if (DbCache::$active) {
					// TODO update dbCache
				}
			}
			if($updateMany && $update){
				if ($manyToManyRelations) {
					self::updateManyToMany ( $manyToManyRelations, $members, $className, $instance, $values );
				}
				if ($oneToManyRelations) {
					self::updateOneToMany ( $oneToManyRelations, $members, $className, $instance, $values );
				}
			}
		}
		return $update;
	}
	
	protected static function setInputValues(string $className,$instance,&$values,$setValues){
		$fieldTypes = OrmUtils::getFieldTypes ( $className );
		foreach ( $fieldTypes as $property => $type ) {
			if (DbTypes::isBoolean($type)) {
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
	}

	private static function getInputValues($values,$index){
		$r=[];
		foreach ($values as $k=>$oValues){
			if($k!=='_status') {
				$r[$k] = $oValues[$index];
			}
		}
		return $r;
	}

	private static function getOneToManyKeys($keys,$values,$index,$defaultId){
		$r=[];
		foreach ( $keys as $k){
			$nk=$values[$k][$index]??$defaultId;
			$r[$k]=$nk;
			if($nk==''){
				return false;
			}
		}
		return $r;
	}

	protected static function updateOneToMany($oneToManyRelations,$members,$className,$instance,$values){
		$id=OrmUtils::getFirstKeyValue($instance);
		$newValues=[];
		foreach ($oneToManyRelations as $name){
			$member=$name.'Ids';
			$len=\strlen($member);
			if(($values[$member]??'')==='updated'){
				foreach ($values as $k=>$v){
					if(\substr($k, 0, $len) === $member){
						$newK=\substr($k,$len+1);
						if($newK!=null) {
							$newValues[$newK] = $v;
						}
					}
				}
				$r=OrmUtils::getAnnotationInfoMember($className,'#oneToMany',$name);
				$fkClass=$r['className'];
				$keys=\array_keys(OrmUtils::getKeyFields($fkClass));
				foreach ($newValues['_status'] as $index=>$status){
					$kv=self::getOneToManyKeys($keys,$newValues,$index,$id);
					if($kv!==false) {
						switch ($status) {
							case 'deleted':
								DAO::deleteById($fkClass,$kv);
								break;
							case 'updated':
								$o = DAO::getById($fkClass, $kv);
								if ($o) {
									$oValues = self::getInputValues($newValues, $index);
									self::setInputValues($fkClass, $o, $oValues, true);
									DAO::update($o);
								}
								break;
							case 'added':
								$o=new $fkClass();
								$oValues = \array_merge($kv,self::getInputValues($newValues, $index));
								self::setInputValues($fkClass, $o, $oValues, true);
								DAO::insert($o);
								break;
						}
					}
				}
			}
		}
	}

	protected static function updateManyToOne($manyToOneRelations, $members, $className, $instance, $values) {
		foreach ( $manyToOneRelations as $member ) {
			if (\array_search ( $member, $members ) !== false) {
				$joinColumn = OrmUtils::getAnnotationInfoMember ( $className, '#joinColumn', $member );
				if ($joinColumn) {
					$fkClass = $joinColumn ['className'];
					$fkField = $joinColumn ['name'];
					if (isset ( $values [$fkField] )) {
						if ($values [$fkField] != null) {
							$fkObject = DAO::getById ( $fkClass, $values ["$fkField"] );
							Reflexion::setMemberValue ( $instance, $member, $fkObject );
						} elseif ($joinColumn ['nullable'] ?? false) {
							Reflexion::setMemberValue ( $instance, $member, null );
						}
					}
				}
			}
		}
	}

	protected static function updateManyToMany($manyToManyRelations, $members, $className, $instance, $values) {
		foreach ( $manyToManyRelations as $member ) {
			if (\array_search ( $member, $members ) !== false) {
				if (($annot = OrmUtils::getAnnotationInfoMember ( $className, '#manyToMany', $member )) !== false) {
					$newField = $member . 'Ids';
					$fkClass = $annot ['targetEntity'];
					$fkObjects = DAO::getAll ( $fkClass, self::getMultiWhere ( $values [$newField], $fkClass ) );
					if (Reflexion::setMemberValue ( $instance, $member, $fkObjects )) {
						DAO::insertOrUpdateManyToMany ( $instance, $member );
					}
				}
			}
		}
	}

	private static function getMultiWhere($ids, $class) {
		$pk = OrmUtils::getFirstKey ( $class );
		$ids = explode ( ',', $ids );
		$idCount = \count ( $ids );
		if ($idCount < 1)
			return '';
		$strs = [ ];
		for($i = 0; $i < $idCount; $i ++) {
			$strs [] = $pk . "='" . $ids [$i] . "'";
		}
		return \implode ( " OR ", $strs );
	}

	public static function getFkIntance($instance, $model, $member, $included = false) {
		$result = [ ];
		if (($annot = OrmUtils::getAnnotationInfoMember ( $model, '#oneToMany', $member )) !== false) {
			$objectFK = DAO::getOneToMany ( $instance, $member, $included );
			$fkClass = $annot ['className'];
		} elseif (($annot = OrmUtils::getAnnotationInfoMember ( $model, '#manyToMany', $member )) !== false) {
			$objectFK = DAO::getManyToMany ( $instance, $member );
			$fkClass = $annot ['targetEntity'];
		} else {
			$objectFK = Reflexion::getMemberValue ( $instance, $member );
			if ($objectFK!=null && ! is_object ( $objectFK )) {
				$objectFK = DAO::getManyToOne ( $instance, $member, $included );
			}
			if (isset ( $objectFK ))
				$fkClass = \get_class ( $objectFK );
		}
		if (isset ( $fkClass )) {
			$fkTable = OrmUtils::getTableName ( $fkClass );
			$result [$member] = compact ( 'objectFK', 'fkClass', 'fkTable' );
		}
		return $result;
	}

	public static function getFKIntances($instance, $model, $included = false) {
		$result = [ ];
		$relations = OrmUtils::getFieldsInRelations ( $model );
		foreach ( $relations as $member ) {
			$fkInstance = self::getFkIntance ( $instance, $model, $member, $included );
			if (\count ( $fkInstance ) > 0) {
				$result = \array_merge ( $result, $fkInstance );
			}
		}
		return $result;
	}
}

