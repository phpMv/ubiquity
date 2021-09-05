<?php

namespace Ubiquity\orm\traits;

use Ubiquity\orm\parser\ManyToManyParser;

/**
 * Trait for DAO relations.
 * Ubiquity\orm\traits$OrmUtilsRelationsTrait
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.8
 *
 */
trait OrmUtilsRelationsTrait {
	
	abstract public static function getAnnotationInfoMember($class, $keyAnnotation, $member);
	
	abstract public static function getAnnotationInfo($class, $keyAnnotation);
	
	abstract public static function getTableName($class);
	
	abstract public static function getFirstKey($class);
	
	abstract public static function getModelMetadata($className);
	
	abstract public static function getKeyFieldsAndValues($instance);
	
	public static function getJoinTables($class) {
		$result = [ ];
		
		if (isset ( self::getModelMetadata ( $class ) ['#joinTable'] )) {
			$jts = self::getModelMetadata ( $class ) ['#joinTable'];
			foreach ( $jts as $jt ) {
				$result [] = $jt ['name'];
			}
		}
		return $result;
	}
	
	public static function getAllJoinTables($models) {
		$result = [ ];
		foreach ( $models as $model ) {
			$result = \array_merge ( $result, self::getJoinTables ( $model ) );
		}
		return $result;
	}
	
	public static function getFieldsInRelations($class) {
		$result = [ ];
		
		if ($manyToOne = self::getAnnotationInfo ( $class, '#manyToOne' )) {
			$result = \array_merge ( $result, $manyToOne );
		}
		if ($oneToMany = self::getAnnotationInfo ( $class, '#oneToMany' )) {
			$result = \array_merge ( $result, \array_keys ( $oneToMany ) );
		}
		if ($manyToMany = self::getAnnotationInfo ( $class, '#manyToMany' )) {
			$result = \array_merge ( $result, \array_keys ( $manyToMany ) );
		}
		return $result;
	}
	
	public static function getRelationInfos($class) {
		$result = [ ];
		$joinColumns = self::getAnnotationInfo ( $class, '#joinColumn' );
		$invertedJoinColumns = self::getAnnotationInfo ( $class, '#invertedJoinColumn' );
		if ($manyToOne = self::getAnnotationInfo ( $class, '#manyToOne' )) {
			foreach ( $manyToOne as $oneField ) {
				$field = $joinColumns [$oneField] ['name'];
				$result [$field] = $invertedJoinColumns [$field];
				$result [$oneField] = $invertedJoinColumns [$field];
			}
		}
		if ($oneToMany = self::getAnnotationInfo ( $class, '#oneToMany' )) {
			$result = \array_merge ( $result, $oneToMany );
		}
		if ($manyToMany = self::getAnnotationInfo ( $class, '#manyToMany' )) {
			$result = \array_merge ( $result, $manyToMany );
		}
		return $result;
	}
	
	public static function getFieldsInRelations_($class) {
		return self::getFieldsInRelationsForUpdate_ ( $class ) ['relations'];
	}
	
	public static function getFieldsInRelationsForUpdate_($class) {
		$result = [ ];
		if ($manyToOne = self::getAnnotationInfo ( $class, '#manyToOne' )) {
			foreach ( $manyToOne as $member ) {
				$joinColumn = self::getAnnotationInfoMember ( $class, '#joinColumn', $member );
				$result [$joinColumn ['name']] = $member;
			}
		}
		if ($manyToMany = self::getAnnotationInfo ( $class, "#manyToMany" )) {
			$manyToMany = array_keys ( $manyToMany );
			foreach ( $manyToMany as $member ) {
				$result [$member . 'Ids'] = $member;
			}
		}
		if ($oneToMany = self::getAnnotationInfo ( $class, '#oneToMany' )) {
			$oneToMany = array_keys ( $oneToMany );
			foreach ( $oneToMany as $member ) {
				$result [$member . 'Ids'] = $member;
			}
		}
		return [ 'relations' => $result,'manyToOne' => $manyToOne,'manyToMany' => $manyToMany,'oneToMany' => $oneToMany ];
	}
	
	public static function getAnnotFieldsInRelations($class) {
		$result = [ ];
		if ($manyToOnes = self::getAnnotationInfo ( $class, '#manyToOne' )) {
			$joinColumns = self::getAnnotationInfo ( $class, '#joinColumn' );
			foreach ( $manyToOnes as $manyToOne ) {
				if (isset ( $joinColumns [$manyToOne] )) {
					$result [$manyToOne] = [ 'type' => 'manyToOne','value' => $joinColumns [$manyToOne] ];
				}
			}
		}
		if ($oneToManys = self::getAnnotationInfo ( $class, '#oneToMany' )) {
			foreach ( $oneToManys as $field => $oneToMany ) {
				$result [$field] = [ 'type' => 'oneToMany','value' => $oneToMany ];
			}
		}
		if ($manyToManys = self::getAnnotationInfo ( $class, "#manyToMany" )) {
			foreach ( $manyToManys as $field => $manyToMany ) {
				$result [$field] = [ 'type' => 'manyToMany','value' => $manyToMany ];
			}
		}
		return $result;
	}
	
	public static function getUJoinSQL($db, $model, $arrayAnnot, $field, &$aliases, $quote) {
		$type = $arrayAnnot ['type'];
		$annot = $arrayAnnot ['value'];
		$table = self::getTableName ( $model );
		$tableAlias = (isset ( $aliases [$table] )) ? $aliases [$table] : $table;
		if ($type === 'manyToOne') {
			$fkClass = $annot ['className'];
			$fkTable = self::getTableName ( $fkClass );
			$fkField = $annot ['name'];
			$pkField = self::getFirstKey ( $fkClass );
			$alias = self::getJoinAlias ( $table, $fkTable );
			$result = "LEFT JOIN {$quote}{$fkTable}{$quote} {$quote}{$alias}{$quote} ON {$quote}{$tableAlias}{$quote}.{$quote}{$fkField}{$quote}={$quote}{$alias}{$quote}.{$quote}{$pkField}{$quote}";
		} elseif ($type === 'oneToMany') {
			$fkClass = $annot ['className'];
			$fkAnnot = self::getAnnotationInfoMember ( $fkClass, '#joinColumn', $annot ['mappedBy'] );
			$fkTable = self::getTableName ( $fkClass );
			$fkField = $fkAnnot ['name'];
			$pkField = self::getFirstKey ( $model );
			$alias = self::getJoinAlias ( $table, $fkTable );
			$result = "LEFT JOIN {$quote}{$fkTable}{$quote} {$quote}{$alias}{$quote} ON {$quote}{$tableAlias}{$quote}.{$quote}{$pkField}{$quote}={$quote}{$alias}{$quote}.{$quote}{$fkField}{$quote}";
		} else {
			$parser = new ManyToManyParser ( $db, $model, $field );
			$parser->init ( $annot );
			$fkTable = $parser->getTargetEntityTable ();
			$fkClass = $parser->getTargetEntityClass ();
			$alias = self::getJoinAlias ( $table, $fkTable );
			$result = $parser->getSQL ( $alias, $aliases );
		}
		
		if (array_search ( $alias, $aliases ) !== false) {
			$result = "";
		}
		$aliases [$fkTable] = $alias;
		return [ 'class' => $fkClass,'table' => $fkTable,'sql' => $result,'alias' => $alias ];
	}
	
	private static function getJoinAlias($table, $fkTable) {
		return \uniqid ( $fkTable . '_' . $table [0] );
	}
	
	public static function getOneToManyFields($class) {
		return self::getAnnotationInfo ( $class, '#oneToMany' );
	}
	
	public static function getRemoveCascadeFields($class,$keyAnnotation='#oneToMany') {
		$infos= self::getAnnotationInfo ( $class, $keyAnnotation);
		$res=[];
		if($infos!==false){
			foreach ($infos as $f=>$annot){
				if(\array_search('remove',$annot['cascade']??[])!==false){
					$res[]=$f;
				}
			}
		}
		return $res;
	}
	
	public static function getManyToOneFields($class) {
		return self::getAnnotationInfo ( $class, '#manyToOne' );
	}
	
	public static function getManyToManyFields($class) {
		$result = self::getAnnotationInfo ( $class, '#manyToMany' );
		if ($result !== false)
			return \array_keys ( $result );
			return [ ];
	}
	
	public static function getDefaultFk($classname) {
		return 'id' . \ucfirst ( self::getTableName ( $classname ) );
	}
	
	public static function getMemberJoinColumns($instance, $member, $metaDatas = NULL) {
		if (! isset ( $metaDatas )) {
			if (\is_object ( $instance )) {
				$class = \get_class ( $instance );
			} else {
				$class = $instance;
			}
			$metaDatas = self::getModelMetadata ( $class );
		}
		$invertedJoinColumns = $metaDatas ['#invertedJoinColumn'];
		foreach ( $invertedJoinColumns as $field => $invertedJoinColumn ) {
			if ($invertedJoinColumn ['member'] === $member) {
				return [ $field,$invertedJoinColumn ];
			}
		}
		return null;
	}
	
	/**
	 *
	 * @param object $instance
	 * @return mixed[]
	 */
	public static function getManyToOneMembersAndValues($instance) {
		$ret = array ();
		$class = \get_class ( $instance );
		$members = self::getAnnotationInfo ( $class, '#manyToOne' );
		if ($members !== false) {
			foreach ( $members as $member ) {
				$memberAccessor = 'get' . ucfirst ( $member );
				if (\method_exists ( $instance, $memberAccessor )) {
					$memberInstance = $instance->$memberAccessor ();
					if (isset ( $memberInstance ) ){
						if(\is_object ( $memberInstance )) {
							$keyValues = self::getKeyFieldsAndValues($memberInstance);
							if (\count($keyValues) > 0) {
								$fkName = self::getJoinColumnName($class, $member);
								$ret [$fkName] = \current($keyValues);
							}
						}else{
							$fkName = self::getJoinColumnName($class, $member);
							$ret [$fkName] = $memberInstance;
						}
					} elseif (self::isNullable ( $class, $member )) {
						$fkName = self::getJoinColumnName ( $class, $member );
						$ret [$fkName] = null;
					}
				}
			}
		}
		return $ret;
	}
	
	public static function getJoinColumnName($class, $member) {
		$annot = self::getAnnotationInfoMember ( $class, '#joinColumn', $member );
		if ($annot !== false) {
			$fkName = $annot ['name'];
		} else {
			$fkName = 'id' . \ucfirst ( self::getTableName ( \ucfirst ( $member ) ) );
		}
		return $fkName;
	}
	
	public static function isManyToMany($class):bool{
		$metas=self::getModelMetadata ( $class );
		$pks=$metas['#primaryKeys'];
		$manyToOnes=$metas['#manyToOne'];
		$manysCount=\count($manyToOnes);
		$counter=0;
		if($manysCount>1) {
			foreach ($manyToOnes as $manyToOne) {
				$len = \strlen($manyToOne);
				foreach ($pks as $k) {
					if (\substr($k, -$len) === \ucfirst($manyToOne)) {
						$counter++;
					}
				}
			}
			return $counter>1;
		}
		return false;
	}
	
	public static function getManyToManyFieldsDt($class,$manyClass) {
		$fields=self::getSerializableMembers($manyClass);
		$joinColumns=self::getModelMetadata($manyClass)['#joinColumn'];
		foreach ($joinColumns as $joinColumn){
			if($joinColumn['className']===$class){
				if($index=\array_search($joinColumn['name'],$fields)!==false){
					unset($fields[$index]);
				}
			}
		}
		return \array_values($fields);
	}
}

