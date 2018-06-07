<?php

namespace Ubiquity\orm\traits;

use Ubiquity\orm\OrmUtils;
use Ubiquity\orm\parser\Reflexion;
use Ubiquity\db\SqlUtils;
use Ubiquity\log\Logger;
use Ubiquity\orm\parser\ManyToManyParser;

/**
 * Trait for DAO Updates (Create, Update, Delete)
 *
 * @author jc
 * @static Database $db
 */
trait DAOUpdatesTrait{

	/**
	 * Deletes the object $instance from the database
	 *
	 * @param object $instance
	 *        	instance à supprimer
	 */
	public static function remove($instance) {
		$tableName = OrmUtils::getTableName ( get_class ( $instance ) );
		$keyAndValues = OrmUtils::getKeyFieldsAndValues ( $instance );
		return self::removeByKey_ ( $tableName, $keyAndValues );
	}

	/**
	 *
	 * @param string $tableName
	 * @param array $keyAndValues
	 * @return int the number of rows that were modified or deleted by the SQL statement you issued
	 */
	private static function removeByKey_($tableName, $keyAndValues) {
		$sql = "DELETE FROM " . $tableName . " WHERE " . SqlUtils::getWhere ( $keyAndValues );
		Logger::log ( "delete", $sql );
		$statement = self::$db->prepareStatement ( $sql );
		foreach ( $keyAndValues as $key => $value ) {
			self::$db->bindValueFromStatement ( $statement, $key, $value );
		}

		return $statement->execute ();
	}

	/**
	 *
	 * @param string $tableName
	 * @param string $where
	 * @return int the number of rows that were modified or deleted by the SQL statement you issued
	 */
	private static function remove_($tableName, $where) {
		$sql = "DELETE FROM " . $tableName . " " . SqlUtils::checkWhere ( $where );
		Logger::log ( "delete", $sql );
		$statement = self::$db->prepareStatement ( $sql );
		if($statement->execute ()){
			return $statement->rowCount();
		}
		return false;
	}
	
	/**
	 * Deletes all instances from $modelName matching the condition $where
	 * @param string $modelName
	 * @param string $where
	 * @return number
	 */
	public static function deleteAll($modelName,$where){
		$tableName = OrmUtils::getTableName ( $modelName );
		return self::remove_($tableName, $where);
	}

	/**
	 * Deletes all instances from $modelName corresponding to $ids
	 * @param string $modelName
	 * @param array|int $ids
	 * @return int
	 */
	public static function delete($modelName, $ids) {
		$tableName = OrmUtils::getTableName ( $modelName );
		$pk = OrmUtils::getFirstKey ( $modelName );
		if (! \is_array ( $ids )) {
			$ids = [ $ids ];
		}
		$where = SqlUtils::getMultiWhere ( $ids, $pk );
		return self::remove_ ( $tableName, $where );
	}

	/**
	 * Inserts a new instance $ instance into the database
	 *
	 * @param object $instance
	 *        	the instance to insert
	 * @param boolean $insertMany
	 *        	if true, save instances related to $instance by a ManyToMany association
	 */
	public static function insert($instance, $insertMany = false) {
		$tableName = OrmUtils::getTableName ( get_class ( $instance ) );
		$keyAndValues = Reflexion::getPropertiesAndValues ( $instance );
		$keyAndValues = array_merge ( $keyAndValues, OrmUtils::getManyToOneMembersAndValues ( $instance ) );
		$sql = "INSERT INTO " . $tableName . "(" . SqlUtils::getInsertFields ( $keyAndValues ) . ") VALUES(" . SqlUtils::getInsertFieldsValues ( $keyAndValues ) . ")";
		Logger::log ( "insert", $sql );
		Logger::log ( "Key and values", json_encode ( $keyAndValues ) );
		$statement = self::$db->prepareStatement ( $sql );
		foreach ( $keyAndValues as $key => $value ) {
			self::$db->bindValueFromStatement ( $statement, $key, $value );
		}
		$result = $statement->execute ();
		if ($result) {
			$accesseurId = "set" . ucfirst ( OrmUtils::getFirstKey ( get_class ( $instance ) ) );
			$instance->$accesseurId ( self::$db->lastInserId () );
			if ($insertMany) {
				self::insertOrUpdateAllManyToMany ( $instance );
			}
		}
		return $result;
	}

	/**
	 * Met à jour les membres de $instance annotés par un ManyToMany
	 *
	 * @param object $instance
	 */
	public static function insertOrUpdateAllManyToMany($instance) {
		$members = OrmUtils::getAnnotationInfo ( get_class ( $instance ), "#manyToMany" );
		if ($members !== false) {
			$members = \array_keys ( $members );
			foreach ( $members as $member ) {
				self::insertOrUpdateManyToMany ( $instance, $member );
			}
		}
	}

	/**
	 * Updates the $member member of $instance annotated by a ManyToMany
	 *
	 * @param Object $instance
	 * @param String $member
	 */
	public static function insertOrUpdateManyToMany($instance, $member) {
		$parser = new ManyToManyParser ( $instance, $member );
		if ($parser->init ()) {
			$myField = $parser->getMyFkField ();
			$field = $parser->getFkField ();
			$sql = "INSERT INTO `" . $parser->getJoinTable () . "`(`" . $myField . "`,`" . $field . "`) VALUES (:" . $myField . ",:" . $field . ");";
			$memberAccessor = "get" . ucfirst ( $member );
			$memberValues = $instance->$memberAccessor ();
			$myKey = $parser->getMyPk ();
			$myAccessorId = "get" . ucfirst ( $myKey );
			$accessorId = "get" . ucfirst ( $parser->getPk () );
			$id = $instance->$myAccessorId ();
			if (! is_null ( $memberValues )) {
				self::$db->execute ( "DELETE FROM `" . $parser->getJoinTable () . "` WHERE `" . $myField . "`='" . $id . "'" );
				$statement = self::$db->prepareStatement ( $sql );
				foreach ( $memberValues as $targetInstance ) {
					$foreignId = $targetInstance->$accessorId ();
					$foreignInstances = self::getAll ( $parser->getTargetEntity (), "`" . $parser->getPk () . "`" . "='" . $foreignId . "'" );
					if (! OrmUtils::exists ( $targetInstance, $parser->getPk (), $foreignInstances )) {
						self::insert ( $targetInstance, false );
						$foreignId = $targetInstance->$accessorId ();
						Logger::log ( "InsertMany", "Insertion d'une instance de " . get_class ( $instance ) );
					}
					self::$db->bindValueFromStatement ( $statement, $myField, $id );
					self::$db->bindValueFromStatement ( $statement, $field, $foreignId );
					$statement->execute ();
					Logger::log ( "InsertMany", "Insertion des valeurs dans la table association '" . $parser->getJoinTable () . "'" );
				}
			}
		}
	}

	/**
	 * Updates an existing $instance in the database.
	 * Be careful not to modify the primary key
	 *
	 * @param object $instance
	 *        	instance to modify
	 * @param boolean $updateMany
	 *        	Adds or updates ManyToMany members
	 */
	public static function update($instance, $updateMany = false) {
		$tableName = OrmUtils::getTableName ( get_class ( $instance ) );
		$ColumnskeyAndValues = Reflexion::getPropertiesAndValues ( $instance );
		$ColumnskeyAndValues = array_merge ( $ColumnskeyAndValues, OrmUtils::getManyToOneMembersAndValues ( $instance ) );
		$keyFieldsAndValues = OrmUtils::getKeyFieldsAndValues ( $instance );
		$sql = "UPDATE " . $tableName . " SET " . SqlUtils::getUpdateFieldsKeyAndValues ( $ColumnskeyAndValues ) . " WHERE " . SqlUtils::getWhere ( $keyFieldsAndValues );
		Logger::log ( "update", $sql );
		Logger::log ( "Key and values", json_encode ( $ColumnskeyAndValues ) );
		$statement = self::$db->prepareStatement ( $sql );
		foreach ( $ColumnskeyAndValues as $key => $value ) {
			self::$db->bindValueFromStatement ( $statement, $key, $value );
		}
		$result = $statement->execute ();
		if ($result && $updateMany)
			self::insertOrUpdateAllManyToMany ( $instance );
		return $result;
	}

	/**
	 *
	 * @param object $instance
	 * @param boolean $updateMany
	 * @return int
	 */
	public static function save($instance, $updateMany = false) {
		if (isset ( $instance->_rest )) {
			return self::update ( $instance, $updateMany );
		}
		return self::insert ( $instance, $updateMany );
	}
}
