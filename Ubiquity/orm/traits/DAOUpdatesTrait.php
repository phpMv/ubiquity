<?php
namespace Ubiquity\orm\traits;

use Ubiquity\orm\OrmUtils;
use Ubiquity\orm\parser\Reflexion;
use Ubiquity\db\SqlUtils;
use Ubiquity\log\Logger;
use Ubiquity\orm\parser\ManyToManyParser;

/**
 * Trait for DAO Updates (Create, Update, Delete)
 * @author jc
 *
 */
trait DAOUpdatesTrait{

	/**
	 * Supprime $instance dans la base de données
	 * @param Classe $instance instance à supprimer
	 */
	public static function remove($instance) {
		$tableName=OrmUtils::getTableName(get_class($instance));
		$keyAndValues=OrmUtils::getKeyFieldsAndValues($instance);
		$sql="DELETE FROM " . $tableName . " WHERE " . SqlUtils::getWhere($keyAndValues);
		Logger::log("delete", $sql);
		$statement=self::$db->prepareStatement($sql);
		foreach ( $keyAndValues as $key => $value ) {
			self::$db->bindValueFromStatement($statement, $key, $value);
		}
		return $statement->execute();
	}

	/**
	 * Inserts a new instance $ instance into the database
	 * @param object the instance to insert
	 * @param $insertMany if true, save instances related to $instance by a ManyToMany association
	 */
	public static function insert($instance, $insertMany=false) {
		$tableName=OrmUtils::getTableName(get_class($instance));
		$keyAndValues=Reflexion::getPropertiesAndValues($instance);
		$keyAndValues=array_merge($keyAndValues, OrmUtils::getManyToOneMembersAndValues($instance));
		$sql="INSERT INTO " . $tableName . "(" . SqlUtils::getInsertFields($keyAndValues) . ") VALUES(" . SqlUtils::getInsertFieldsValues($keyAndValues) . ")";
		Logger::log("insert", $sql);
		Logger::log("Key and values", json_encode($keyAndValues));
		$statement=self::$db->prepareStatement($sql);
		foreach ( $keyAndValues as $key => $value ) {
			self::$db->bindValueFromStatement($statement, $key, $value);
		}
		$result=$statement->execute();
		if ($result) {
			$accesseurId="set" . ucfirst(OrmUtils::getFirstKey(get_class($instance)));
			$instance->$accesseurId(self::$db->lastInserId());
			if ($insertMany) {
				self::insertOrUpdateAllManyToMany($instance);
			}
		}
		return $result;
	}

	/**
	 * Met à jour les membres de $instance annotés par un ManyToMany
	 * @param object $instance
	 */
	public static function insertOrUpdateAllManyToMany($instance) {
		$members=OrmUtils::getAnnotationInfo(get_class($instance), "#manyToMany");
		if ($members !== false) {
			$members=\array_keys($members);
			foreach ( $members as $member ) {
				self::insertOrUpdateManyToMany($instance, $member);
			}
		}
	}

	/**
	 * Updates the $member member of $instance annotated by a ManyToMany
	 * @param Object $instance
	 * @param String $member
	 */
	public static function insertOrUpdateManyToMany($instance, $member) {
		$parser=new ManyToManyParser($instance, $member);
		if ($parser->init()) {
			$myField=$parser->getMyFkField();
			$field=$parser->getFkField();
			$sql="INSERT INTO `" . $parser->getJoinTable() . "`(`" . $myField . "`,`" . $field . "`) VALUES (:" . $myField . ",:" . $field . ");";
			$memberAccessor="get" . ucfirst($member);
			$memberValues=$instance->$memberAccessor();
			$myKey=$parser->getMyPk();
			$myAccessorId="get" . ucfirst($myKey);
			$accessorId="get" . ucfirst($parser->getPk());
			$id=$instance->$myAccessorId();
			if (!is_null($memberValues)) {
				self::$db->execute("DELETE FROM `" . $parser->getJoinTable() . "` WHERE `" . $myField . "`='" . $id . "'");
				$statement=self::$db->prepareStatement($sql);
				foreach ( $memberValues as $targetInstance ) {
					$foreignId=$targetInstance->$accessorId();
					$foreignInstances=self::getAll($parser->getTargetEntity(), "`" . $parser->getPk() . "`" . "='" . $foreignId . "'");
					if (!OrmUtils::exists($targetInstance, $parser->getPk(), $foreignInstances)) {
						self::insert($targetInstance, false);
						$foreignId=$targetInstance->$accessorId();
						Logger::log("InsertMany", "Insertion d'une instance de " . get_class($instance));
					}
					self::$db->bindValueFromStatement($statement, $myField, $id);
					self::$db->bindValueFromStatement($statement, $field, $foreignId);
					$statement->execute();
					Logger::log("InsertMany", "Insertion des valeurs dans la table association '" . $parser->getJoinTable() . "'");
				}
			}
		}
	}

	/**
	 * Updates an existing $instance in the database.
	 * Be careful not to modify the primary key
	 * @param Classe $instance instance to modify
	 * @param $updateMany Adds or updates ManyToMany members
	 */
	public static function update($instance, $updateMany=false) {
		$tableName=OrmUtils::getTableName(get_class($instance));
		$ColumnskeyAndValues=Reflexion::getPropertiesAndValues($instance);
		$ColumnskeyAndValues=array_merge($ColumnskeyAndValues, OrmUtils::getManyToOneMembersAndValues($instance));
		$keyFieldsAndValues=OrmUtils::getKeyFieldsAndValues($instance);
		$sql="UPDATE " . $tableName . " SET " . SqlUtils::getUpdateFieldsKeyAndValues($ColumnskeyAndValues) . " WHERE " . SqlUtils::getWhere($keyFieldsAndValues);
		Logger::log("update", $sql);
		Logger::log("Key and values", json_encode($ColumnskeyAndValues));
		$statement=self::$db->prepareStatement($sql);
		foreach ( $ColumnskeyAndValues as $key => $value ) {
			self::$db->bindValueFromStatement($statement, $key, $value);
		}
		$result=$statement->execute();
		if ($result && $updateMany)
			self::insertOrUpdateAllManyToMany($instance);
			return $result;
	}
}
