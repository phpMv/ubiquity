<?php

namespace Ubiquity\orm\traits;

use Ubiquity\db\SqlUtils;
use Ubiquity\events\DAOEvents;
use Ubiquity\events\EventsManager;
use Ubiquity\log\Logger;
use Ubiquity\orm\OrmUtils;
use Ubiquity\orm\parser\ManyToManyParser;
use Ubiquity\orm\parser\Reflexion;
use Ubiquity\controllers\Startup;

/**
 * Trait for DAO Updates (Create, Update, Delete)
 * Ubiquity\orm\traits$DAOUpdatesTrait
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.1.5
 * @property \Ubiquity\db\Database $db
 *
 */
trait DAOUpdatesTrait {

	/**
	 * Deletes the object $instance from the database
	 *
	 * @param object $instance instance à supprimer
	 */
	public static function remove($instance): ?int {
		$className = \get_class ( $instance );
		$tableName = OrmUtils::getTableName ( $className );
		$keyAndValues = OrmUtils::getKeyFieldsAndValues ( $instance );
		return self::removeByKey_ ( $className, $tableName, $keyAndValues );
	}

	/**
	 *
	 * @param string $className
	 * @param string $tableName
	 * @param array $keyAndValues
	 * @return int the number of rows that were modified or deleted by the SQL statement you issued
	 */
	private static function removeByKey_($className, $tableName, $keyAndValues): ?int {
		$db = self::getDb ( $className );
		$sql = 'DELETE FROM ' . $db->quote . $tableName . $db->quote . ' WHERE ' . SqlUtils::getWhere ( $keyAndValues );
		Logger::info ( 'DAOUpdates', $sql, 'delete' );
		$statement = $db->prepareStatement ( $sql );
		try {
			if ($statement->execute ( $keyAndValues )) {
				return $statement->rowCount ();
			}
		} catch ( \PDOException $e ) {
			Logger::warn ( 'DAOUpdates', $e->getMessage (), 'delete' );
			return null;
		}
		return 0;
	}

	/**
	 *
	 * @param \Ubiquity\db\Database $db
	 * @param string $className
	 * @param string $tableName
	 * @param string $where
	 * @param array $params
	 * @return boolean|int the number of rows that were modified or deleted by the SQL statement you issued
	 */
	private static function remove_($db, $tableName, $where, $params) {
		$sql = 'DELETE FROM ' . $tableName . ' ' . SqlUtils::checkWhere ( $where );
		Logger::info ( 'DAOUpdates', $sql, 'delete' );
		$statement = $db->prepareStatement ( $sql );
		try {
			if ($statement->execute ( $params )) {
				return $statement->rowCount ();
			}
		} catch ( \PDOException $e ) {
			Logger::warn ( 'DAOUpdates', $e->getMessage (), 'delete' );
			return false;
		}
	}

	/**
	 * Deletes all instances from $modelName matching the condition $where
	 *
	 * @param string $modelName
	 * @param string $where
	 * @param array $params
	 * @return int|boolean
	 */
	public static function deleteAll($modelName, $where, $params = [ ]) {
		$db = self::getDb ( $modelName );
		$quote = $db->quote;
		$tableName = OrmUtils::getTableName ( $modelName );
		return self::remove_ ( $db, $quote . $tableName . $quote, $where, $params );
	}

	/**
	 * Deletes all instances from $modelName corresponding to $ids
	 *
	 * @param string $modelName
	 * @param array|int $ids
	 * @return int|boolean
	 */
	public static function delete($modelName, $ids) {
		$tableName = OrmUtils::getTableName ( $modelName );
		$db = self::getDb ( $modelName );
		$pk = OrmUtils::getFirstKey ( $modelName );
		if (! \is_array ( $ids )) {
			$ids = [ $ids ];
		}
		$quote = $db->quote;
		$count = \count ( $ids );
		$r = $quote . $pk . $quote . "= ?";
		return self::remove_ ( $db, $quote . $tableName . $quote, \str_repeat ( "$r OR", $count - 1 ) . $r, $ids );
	}

	/**
	 * Inserts a new instance $instance into the database
	 *
	 * @param object $instance the instance to insert
	 * @param boolean $insertMany if true, save instances related to $instance by a ManyToMany association
	 */
	public static function insert($instance, $insertMany = false) {
		EventsManager::trigger ( 'dao.before.insert', $instance );
		$className = \get_class ( $instance );
		$db = self::getDb ( $className );
		$quote = $db->quote;
		$tableName = OrmUtils::getTableName ( $className );
		$keyAndValues = Reflexion::getPropertiesAndValues ( $instance );
		$keyAndValues = array_merge ( $keyAndValues, OrmUtils::getManyToOneMembersAndValues ( $instance ) );
		$pk = OrmUtils::getFirstKey ( $className );
		if (($keyAndValues [$pk] ?? null) == null) {
			unset ( $keyAndValues [$pk] );
		}
		$sql = "INSERT INTO {$quote}{$tableName}{$quote} (" . SqlUtils::getInsertFields ( $keyAndValues ) . ') VALUES(' . SqlUtils::getInsertFieldsValues ( $keyAndValues ) . ')';
		if (Logger::isActive ()) {
			Logger::info ( 'DAOUpdates', $sql, 'insert' );
			Logger::info ( 'DAOUpdates', \json_encode ( $keyAndValues ), 'Key and values' );
		}

		$statement = $db->getUpdateStatement ( $sql );
		try {
			$result = $statement->execute ( $keyAndValues );
			if ($result) {
				$accesseurId = 'set' . \ucfirst ( $pk );
				$lastId = $db->lastInserId ( "{$tableName}_{$pk}_seq" );
				if ($lastId != 0) {
					$instance->$accesseurId ( $lastId );
					$instance->_rest = $keyAndValues;
					$instance->_rest [$pk] = $lastId;
				}
				if ($insertMany) {
					self::insertOrUpdateAllManyToMany ( $instance );
				}
			}
			EventsManager::trigger ( DAOEvents::AFTER_INSERT, $instance, $result );
			return $result;
		} catch ( \Exception $e ) {
			Logger::warn ( 'DAOUpdates', $e->getMessage (), 'insert' );
			if (Startup::$config ['debug']) {
				throw $e;
			}
		}
		return false;
	}

	/**
	 * Updates manyToMany members
	 *
	 * @param object $instance
	 */
	public static function insertOrUpdateAllManyToMany($instance) {
		$members = OrmUtils::getAnnotationInfo ( get_class ( $instance ), '#manyToMany' );
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
		$db = self::getDb ( \get_class ( $instance ) );
		$parser = new ManyToManyParser ( $db, $instance, $member );
		if ($parser->init ()) {
			$quote = $db->quote;
			$myField = $parser->getMyFkField ();
			$field = $parser->getFkField ();
			$sql = "INSERT INTO {$quote}" . $parser->getJoinTable () . "{$quote}({$quote}" . $myField . "{$quote},{$quote}" . $field . "{$quote}) VALUES (:" . $myField . ",:" . $field . ");";
			$memberAccessor = 'get' . \ucfirst ( $member );
			$memberValues = $instance->$memberAccessor ();
			$myKey = $parser->getMyPk ();
			$myAccessorId = 'get' . \ucfirst ( $myKey );
			$accessorId = 'get' . \ucfirst ( $parser->getPk () );
			$id = $instance->$myAccessorId ();
			if (! \is_null ( $memberValues )) {
				$db->execute ( "DELETE FROM {$quote}" . $parser->getJoinTable () . "{$quote} WHERE {$quote}{$myField}{$quote}='{$id}'" );
				$statement = $db->prepareStatement ( $sql );
				foreach ( $memberValues as $targetInstance ) {
					$foreignId = $targetInstance->$accessorId ();
					$foreignInstances = self::getAll ( $parser->getTargetEntity (), $quote . $parser->getPk () . $quote . "='{$foreignId}'" );
					if (! OrmUtils::exists ( $targetInstance, $parser->getPk (), $foreignInstances )) {
						self::insert ( $targetInstance, false );
						$foreignId = $targetInstance->$accessorId ();
						Logger::info ( 'DAOUpdates', "Insertion d'une instance de " . get_class ( $instance ), 'InsertMany' );
					}
					$db->bindValueFromStatement ( $statement, $myField, $id );
					$db->bindValueFromStatement ( $statement, $field, $foreignId );
					$statement->execute ();
					Logger::info ( 'DAOUpdates', "Insertion des valeurs dans la table association '" . $parser->getJoinTable () . "'", 'InsertMany' );
				}
			}
		}
	}

	/**
	 * Updates an existing $instance in the database.
	 * Be careful not to modify the primary key
	 *
	 * @param object $instance instance to modify
	 * @param boolean $updateMany Adds or updates ManyToMany members
	 */
	public static function update($instance, $updateMany = false) {
		EventsManager::trigger ( 'dao.before.update', $instance );
		$className = \get_class ( $instance );
		$db = self::getDb ( $className );
		$quote = $db->quote;
		$tableName = OrmUtils::getTableName ( $className );
		$ColumnskeyAndValues = \array_merge ( Reflexion::getPropertiesAndValues ( $instance ), OrmUtils::getManyToOneMembersAndValues ( $instance ) );
		$keyFieldsAndValues = OrmUtils::getKeyFieldsAndValues ( $instance );
		$sql = "UPDATE {$quote}{$tableName}{$quote} SET " . SqlUtils::getUpdateFieldsKeyAndParams ( $ColumnskeyAndValues ) . ' WHERE ' . SqlUtils::getWhere ( $keyFieldsAndValues );
		if (Logger::isActive ()) {
			Logger::info ( "DAOUpdates", $sql, "update" );
			Logger::info ( "DAOUpdates", json_encode ( $ColumnskeyAndValues ), "Key and values" );
		}
		$statement = $db->getUpdateStatement ( $sql );
		try {
			$result = $statement->execute ( $ColumnskeyAndValues );
			if ($updateMany && $result) {
				self::insertOrUpdateAllManyToMany ( $instance );
			}
			EventsManager::trigger ( DAOEvents::AFTER_UPDATE, $instance, $result );
			$instance->_rest = \array_merge ( $instance->_rest, $ColumnskeyAndValues );
			return $result;
		} catch ( \Exception $e ) {
			Logger::warn ( "DAOUpdates", $e->getMessage (), "update" );
		}
		return false;
	}

	/**
	 * Updates an array of $instances in the database.
	 * Be careful not to modify the primary key
	 *
	 * @param array $instances instances to modify
	 * @param boolean $updateMany Adds or updates ManyToMany members
	 * @return boolean
	 */
	public static function updateGroup($instances, $updateMany = false) {
		if (\count ( $instances ) > 0) {
			$instance = \current ( $instances );
			$className = \get_class ( $instance );
			$db = self::getDb ( $className );
			$quote = $db->quote;
			$tableName = OrmUtils::getTableName ( $className );
			$ColumnskeyAndValues = \array_merge ( Reflexion::getPropertiesAndValues ( $instance ), OrmUtils::getManyToOneMembersAndValues ( $instance ) );
			$keyFieldsAndValues = OrmUtils::getKeyFieldsAndValues ( $instance );
			$sql = "UPDATE {$quote}{$tableName}{$quote} SET " . SqlUtils::getUpdateFieldsKeyAndParams ( $ColumnskeyAndValues ) . ' WHERE ' . SqlUtils::getWhere ( $keyFieldsAndValues );

			$statement = $db->getUpdateStatement ( $sql );
			try {
				$db->beginTransaction ();
				foreach ( $instances as $instance ) {
					EventsManager::trigger ( 'dao.before.update', $instance );
					$ColumnskeyAndValues = \array_merge ( Reflexion::getPropertiesAndValues ( $instance ), OrmUtils::getManyToOneMembersAndValues ( $instance ) );
					$result = $statement->execute ( $ColumnskeyAndValues );
					if ($updateMany && $result) {
						self::insertOrUpdateAllManyToMany ( $instance );
					}
					EventsManager::trigger ( DAOEvents::AFTER_UPDATE, $instance, $result );
					$instance->_rest = \array_merge ( $instance->_rest, $ColumnskeyAndValues );
					if (Logger::isActive ()) {
						Logger::info ( "DAOUpdates", $sql, "update" );
						Logger::info ( "DAOUpdates", json_encode ( $ColumnskeyAndValues ), "Key and values" );
					}
				}
				$db->commit ();
				return true;
			} catch ( \Exception $e ) {
				Logger::warn ( "DAOUpdates", $e->getMessage (), "update" );
				$db->rollBack ();
			}
		}
		return false;
	}

	/**
	 *
	 * @param object $instance
	 * @param boolean $updateMany
	 * @return boolean|int
	 */
	public static function save($instance, $updateMany = false) {
		if (isset ( $instance->_rest )) {
			return self::update ( $instance, $updateMany );
		}
		return self::insert ( $instance, $updateMany );
	}
}
