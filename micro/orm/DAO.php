<?php
namespace micro\orm;
use micro\annotations\ManyToManyParser;
use micro\db\SqlUtils;
use micro\db\Database;
use micro\log\Logger;

/**
 * Classe passerelle entre base de données et modèle objet
 * @author jc
 * @version 1.0.0.4
 * @package orm
 */
class DAO {
	public static $db;
	private static $objects;

	private static function getObjects(){
		if(is_null(DAO::$objects)){
			DAO::$objects=array();
			Logger::log("getObjects","Instanciation de Objects");
		}
		return DAO::$objects;
	}

	private static function getInstanceIdInObjects($instance){
		$condition=DAO::getCondition(OrmUtils::getKeyFieldsAndValues($instance));
		$condition=preg_replace('/\s|\'+/', '', $condition);
		return get_class($instance)."#".$condition;
	}

	private static function getCondition($keyValues){
		$condition="";
		$retArray=array();
		if(is_array($keyValues)){
			foreach ($keyValues as $key=>$value){
				$retArray[]="`".$key."` = '".$value."'";
			}
			$condition=implode(" AND ", $retArray);
		}else
			$condition=$keyValues;
		return $condition;
	}

	private static function addInstanceInObjects($instance){
		DAO::getObjects();
		DAO::$objects[DAO::getInstanceIdInObjects($instance)]=$instance;
	}

	private static function getInstanceInObjects($className,$keyValue){
		$objects=DAO::getObjects();
		$condition=DAO::getCondition($keyValue);
		$condition=preg_replace('/\s|\'+/', '', $condition);
		$key=$className."#".$condition;
		if(array_key_exists($key,$objects)){
			Logger::log("getInstanceInObjects", "Récupération d'une instance de ".$className."(".$condition.") dans objects");
			return $objects[$key];
		}
		return null;
	}

	/**
	 * Charge les membres associés à $instance par une relation de type ManyToOne
	 * @param Classe $instance
	 * @param array $keyValues
	 * @param array $members
	 */
	private static function getOneManyToOne($instance,$keyValues,$members){
		$class=get_class($instance);
		foreach ($members as $member){
			$annot=Reflexion::getAnnotationMember($class, $member->getName(), "JoinColumn");
			if($annot!=false){
				reset($keyValues);
				if($annot->name==key($keyValues)){
					$key=OrmUtils::getFirstKey($annot->className);
					$kv=array($key=>$keyValues[$annot->name]);

					$obj=DAO::getOne($annot->className, $kv,false);
					if($obj!=null){
						Logger::log("getOneManyToOne", "Chargement de ".$member->getName()." pour l'objet ".$class);
						$accesseur="set".ucfirst($member->getName());
						if(method_exists($instance,$accesseur)){
							$instance->$accesseur($obj);
							return;
						}
					}
				}
			}
		}
	}

	/**
	 * Affecte/charge les enregistrements fils dans le membre $member de $instance.
	 * Si $array est null, les fils sont chargés depuis la base de données
	 * @param Classe $instance
	 * @param string $member Membre sur lequel doit être présent une annotation OneToMany
	 * @param array $array paramètre facultatif contenant la liste des fils possibles
	 */
	public static function getOneToMany($instance,$member,$array=null){
		$ret=array();
		$class=get_class($instance);
		$annot=Reflexion::getAnnotationMember($class, $member, "OneToMany");
		if($annot!=false){
			$fk=Reflexion::getAnnotationMember($annot->className, $annot->mappedBy, "JoinColumn");
			$fkv=OrmUtils::getFirstKeyValue($instance);
			if(is_null($array)){
				$ret=DAO::getAll($annot->className,$fk->name."='".$fkv."'");
			}
			else{
				$elementAccessor="get".ucfirst($annot->mappedBy);
				foreach ($array as $element){
					$elementRef=$element->$elementAccessor();
					if(!is_null($elementRef)){
						$idElementRef=OrmUtils::getFirstKeyValue($elementRef);
						if($idElementRef==$fkv)
							$ret[]=$element;
					}
				}
			}
			$accessor="set".ucfirst($member);
			if(method_exists($instance,$accessor)){
				Logger::log("getOneToMany", "Chargement de ".$member." pour l'objet ".$class);
				$instance->$accessor($ret);
			}else{
				Logger::warn("getOneToMany", "L'accesseur ".$accessor." est manquant pour ".$class);
			}

		}
		return $ret;
	}
	private static function getSQLForJoinTable($instance,ManyToManyParser $parser){
		$accessor="get".ucfirst($parser->getPk());
		$sql="SELECT * FROM `".$parser->getJoinTable()."` WHERE `".$parser->getMyFkField()."`='".$instance->$accessor()."'";
		Logger::log("ManyToMany", "Exécution de ".$sql);
		return DAO::$db->query($sql);
	}
	/**
	 * Affecte/charge les enregistrements fils dans le membre $member de $instance.
	 * Si $array est null, les fils sont chargés depuis la base de données
	 * @param Classe $instance
	 * @param string $member Membre sur lequel doit être présent une annotation OneToMany
	 * @param array $array paramètre facultatif contenant la liste des fils possibles
	 */
	public static function getManyToMany($instance,$member,$array=null){
		$ret=array();
		$class=get_class($instance);
		$parser=new ManyToManyParser($instance, $member);
		if($parser->init()){
			$joinTableCursor=DAO::getSQLForJoinTable($instance,$parser);
			if(is_null($array)){
				foreach($joinTableCursor as $row){
					$fkv=$row[$parser->getFkField()];
					$tmp=DAO::getOne($parser->getTargetEntity(),"`".$parser->getPk()."`='".$fkv."'");
					array_push($ret,$tmp);
				}
			}
			else{
				$continue=true;
				$accessorToMember="get".ucfirst($parser->getInversedBy());
				$myPkAccessor="get".ucfirst($parser->getMyPk());

				if(!method_exists($instance, $myPkAccessor)){
					Logger::warn("ManyToMany", "L'accesseur au membre clé primaire ".$myPkAccessor." est manquant pour ".$class);
				}
				if(count($array)>0)
					$continue=method_exists($array[0], $accessorToMember);
				if($continue){
					foreach($joinTableCursor as $row){
						$fkv=$row[$parser->getFkField()];
						foreach($array as $targetEntityInstance){
							$instances=$targetEntityInstance->$accessorToMember();
							if(is_array($instances)){
								foreach ($instances as $inst){
									if($inst->$myPkAccessor==$instance->$myPkAccessor)
										array_push($array, $targetEntityInstance);
								}
							}
						}
					}
				}else{
					Logger::warn("ManyToMany", "L'accesseur au membre ".$parser->getInversedBy()." est manquant pour ".$parser->getTargetEntity());
				}
			}
			$accessor="set".ucfirst($member);

			if(method_exists($instance,$accessor)){
				Logger::log("getManyToMany", "Chargement de ".$member." pour l'objet ".$class);
				$instance->$accessor($ret);
			}else{
				Logger::warn("getManyToMany", "L'accesseur ".$accessor." est manquant pour ".$class);
			}

		}
		return $ret;
	}
	/**
	 * Retourne un tableau d'objets de $className depuis la base de données
	 * @param string $className nom de la classe du model à charger
	 * @param string $condition Partie suivant le WHERE d'une instruction SQL
	 * @return multitype:$className
	 */
	public static function getAll($className,$condition='',$loadManyToOne=true){
		$objects=array();
		$membersManyToOne=Reflexion::getMembersWithAnnotation($className, "ManyToOne");
		$tableName=OrmUtils::getTableName($className);
		if($condition!='')
			$condition=" WHERE ".$condition;
		$query=DAO::$db->query("SELECT * FROM ".$tableName.$condition);
		Logger::log("getAll","SELECT * FROM ".$tableName.$condition);
		foreach ($query as $row){
			//Pour chaque enregistrement : instanciation d'un objet
			$o=new $className();
			$objects[]=$o;
			foreach ($row as $k=>$v){
				//Modificateur et test de son existance
				if(!is_numeric($k)){
					$accesseur="set".ucfirst($k);
					if(method_exists($o,$accesseur)){
						$o->$accesseur($v);
					}
					if($loadManyToOne===true && OrmUtils::isMemberInManyToOne($className,$membersManyToOne, $k)) {
						DAO::getOneManyToOne($o, array($k=>$v), $membersManyToOne);
					}
				}
			}
			DAO::addInstanceInObjects($o);
		}
		return $objects;
	}
	/**
	 * Retourne le nombre d'objets de $className depuis la base de données respectant la condition éventuellement passée en paramètre
	 * @param string $className nom de la classe du model à charger
	 * @param string $condition Partie suivant le WHERE d'une instruction SQL
	 */
	public static function count($className,$condition=''){
		$tableName=OrmUtils::getTableName($className);
		if($condition!='')
			$condition=" WHERE ".$condition;
		return DAO::$db->query("SELECT COUNT(*) FROM ".$tableName.$condition)->fetchColumn();
	}

	/**
	 * Retourne une instance de $className depuis la base de données, à  partir des valeurs $keyValues de la clé primaire
	 * @param String $className nom de la classe du model à charger
	 * @param Array|string $keyValues valeurs des clés primaires ou condition
	 */
	public static function getOne($className,$keyValues,$loadManyToOne=true){
		if(!is_array($keyValues)){
			if(strrpos($keyValues,"=")===false){
				$keyValues="`".OrmUtils::getFirstKey($className)."`='".$keyValues."'";
			}elseif ($keyValues=="")
				$keyValues="";
		}
		$condition=DAO::getCondition($keyValues);
		$retour=DAO::getInstanceInObjects($className,$condition);
		if(!isset($retour)){
			$retour=DAO::getAll($className,$condition,$loadManyToOne);
			if(sizeof($retour)<1)
				return null;
			else
				return $retour[0];
		}
		return $retour;

	}

	/**
	 * Supprime $instance dans la base de données
	 * @param Classe $instance instance à supprimer
	 */
	public static function delete($instance){
		$tableName=OrmUtils::getTableName(get_class($instance));
		$keyAndValues=OrmUtils::getKeyFieldsAndValues($instance);
		$sql="DELETE FROM ".$tableName." WHERE ".SqlUtils::getWhere($keyAndValues);
		Logger::log("delete", $sql);
		$statement=DAO::$db->prepareStatement($sql);
		foreach ($keyAndValues as $key=>$value){
			DAO::$db->bindValueFromStatement($statement,$key,$value);
		}
		return $statement->execute();
	}

	/**
	 * Insère $instance dans la base de données
	 * @param Classe $instance instance à insérer
	 * @param $insertMany si vrai, sauvegarde des instances reliées à $instance par un ManyToMany
	 */
	public static function insert($instance,$insertMany=false){
		$tableName=OrmUtils::getTableName(get_class($instance));
		$keyAndValues=Reflexion::getPropertiesAndValues($instance);
		$keyAndValues=array_merge($keyAndValues, OrmUtils::getManyToOneMembersAndValues($instance));
		$sql="INSERT INTO ".$tableName."(".SqlUtils::getInsertFields($keyAndValues).") VALUES(".SqlUtils::getInsertFieldsValues($keyAndValues).")";
		Logger::log("insert", $sql);
		Logger::log("Key and values", json_encode($keyAndValues));
		$statement=DAO::$db->prepareStatement($sql);
		foreach ($keyAndValues as $key=>$value){
				DAO::$db->bindValueFromStatement($statement,$key,$value);
		}
		$result=$statement->execute();
		if($result){
			$accesseurId="set".ucfirst(OrmUtils::getFirstKey(get_class($instance)));
			$instance->$accesseurId(DAO::$db->lastInserId());
			if($insertMany){
				DAO::insertOrUpdateAllManyToMany($instance);
			}
		}
		return $result;
	}

	/**
	 * Met à jour les membres de $instance annotés par un ManyToMany
	 * @param Object $instance
	 */
	public static function insertOrUpdateAllManyToMany($instance){
		$members=Reflexion::getMembersWithAnnotation(get_class($instance), "ManyToMany");
		foreach ($members as $member){
			DAO::insertOrUpdateManyToMany($instance, $member->name);
		}
	}

	/**
	 * Met à jour le membre $member de $instance annoté par un ManyToMany
	 * @param Object $instance
	 * @param String $member
	 */
	public static function insertOrUpdateManyToMany($instance,$member){
		$parser=new ManyToManyParser($instance, $member);
		if($parser->init()){
			$myField=$parser->getMyFkField();
			$field=$parser->getFkField();
			$sql="INSERT INTO `".$parser->getJoinTable()."`(`".$myField."`,`".$field."`) VALUES (:".$myField.",:".$field.");";
			$memberAccessor="get".ucfirst($member);
			$memberValues=$instance->$memberAccessor();
			$myKey=$parser->getMyPk();
			$myAccessorId="get".ucfirst($myKey);
			$accessorId="get".ucfirst($parser->getPk());
			$id=$instance->$myAccessorId();
			if(!is_null($memberValues)){
				DAO::$db->execute("DELETE FROM `".$parser->getJoinTable()."` WHERE `".$myField."`='".$id."'");
				$statement=DAO::$db->prepareStatement($sql);
				foreach ($memberValues as $k=>$targetInstance){
					$foreignId=$targetInstance->$accessorId();
					$foreignInstances=DAO::getAll($parser->getTargetEntity(), "`".$parser->getPk()."`"."='".$foreignId."'");
					if(!OrmUtils::exists($targetInstance, $parser->getPk(), $foreignInstances)){
						DAO::insert($targetInstance,false);
						$foreignId=$targetInstance->$accessorId();
						Logger::log("InsertMany", "Insertion d'une instance de ".get_class($instance));
					}
					DAO::$db->bindValueFromStatement($statement,$myField,$id);
					DAO::$db->bindValueFromStatement($statement,$field,$foreignId);
					$result=$statement->execute();
					Logger::log("InsertMany", "Insertion des valeurs dans la table association '".$parser->getJoinTable()."'");
				}
			}
		}
	}
	/**
	 * Met à jour $instance dans la base de données.
	 * Attention de ne pas modifier la clé primaire
	 * @param Classe $instance instance à modifier
	 * @param $updateMany Ajoute ou met à jour les membres ManyToMany
	 */
	public static function update($instance,$updateMany=false){
		$tableName=OrmUtils::getTableName(get_class($instance));
		$ColumnskeyAndValues=Reflexion::getPropertiesAndValues($instance);
		$ColumnskeyAndValues=array_merge($ColumnskeyAndValues, OrmUtils::getManyToOneMembersAndValues($instance));
		$keyFieldsAndValues=OrmUtils::getKeyFieldsAndValues($instance);
		$sql="UPDATE ".$tableName." SET ".SqlUtils::getUpdateFieldsKeyAndValues($ColumnskeyAndValues)." WHERE ".SqlUtils::getWhere($keyFieldsAndValues);
		Logger::log("update", $sql);
		Logger::log("Key and values", json_encode($ColumnskeyAndValues));
		$statement=DAO::$db->prepareStatement($sql);
		foreach ($ColumnskeyAndValues as $key=>$value){
				DAO::$db->bindValueFromStatement($statement,$key,$value);
		}
		$result= $statement->execute();
		if($result && $updateMany)
			DAO::insertOrUpdateAllManyToMany($instance);
		return $result;
	}

	/**
	 * Réalise la connexion à la base de données en utilisant les paramètres passés
	 * @param string $dbName
	 * @param string $serverName
	 * @param string $port
	 * @param string $user
	 * @param string $password
	 */
	public static function connect($dbName,$serverName="127.0.0.1",$port="3306",$user="root",$password=""){
		DAO::$db=new Database($dbName,$serverName,$port,$user,$password);
		DAO::$db->connect();
	}
}
