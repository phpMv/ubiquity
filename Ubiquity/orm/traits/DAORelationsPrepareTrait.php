<?php

namespace Ubiquity\orm\traits;

use Ubiquity\orm\OrmUtils;
use Ubiquity\orm\core\PendingRelationsRequest;
use Ubiquity\orm\parser\ManyToManyParser;

trait DAORelationsPrepareTrait {
	
	/**
	 * Prepares members associated with $instance with a ManyToMany type relationship
	 * @param $ret array of sql conditions
	 * @param object $instance
	 * @param string $member Member on which a ManyToMany annotation must be present
	 * @param array $annot used internally
	 */
	protected static function prepareManyToMany(&$ret,$instance, $member, $annot=null) {
		$class=get_class($instance);
		if (!isset($annot)){
			$annot=OrmUtils::getAnnotationInfoMember($class, "#ManyToMany", $member);
		}
		if ($annot !== false) {
			$key=$annot["targetEntity"]."|".$member."|".$annot["inversedBy"];
			if(!isset($ret[$key])){
				$parser=new ManyToManyParser($instance, $member);
				$parser->init($annot);
				$ret[$key]=$parser;
			}
			$accessor="get" . ucfirst($ret[$key]->getMyPk());
			if(method_exists($instance, $accessor)){
				$fkv=$instance->$accessor();
				$ret[$key]->addValue($fkv);
			}
		}
	}
	
	/**
	 * Prepares members associated with $instance with a oneToMany type relationship
	 * @param $ret array of sql conditions
	 * @param object $instance
	 * @param string $member Member on which a OneToMany annotation must be present
	 * @param array $annot used internally
	 */
	protected static function prepareOneToMany(&$ret,$instance, $member, $annot=null) {
		$class=get_class($instance);
		if (!isset($annot))
			$annot=OrmUtils::getAnnotationInfoMember($class, "#oneToMany", $member);
			if ($annot !== false) {
				$fkAnnot=OrmUtils::getAnnotationInfoMember($annot["className"], "#joinColumn", $annot["mappedBy"]);
				if ($fkAnnot !== false) {
					$fkv=OrmUtils::getFirstKeyValue($instance);
					$key=$annot["className"]."|".$member."|".$annot["mappedBy"];
					if(!isset($ret[$key])){
						$ret[$key]=new PendingRelationsRequest();
					}
					$ret[$key]->addPartObject($instance,$fkAnnot["name"] . "= ?",$fkv);
				}
			}
	}
	
	/**
	 * Prepares members associated with $instance with a manyToOne type relationship
	 * @param $ret array of sql conditions
	 * @param object $instance
	 * @param mixed $value
	 * @param string $fkField
	 * @param array $annotationArray
	 */
	protected static function prepareManyToOne(&$ret, $instance,$value, $fkField,$annotationArray) {
		$member=$annotationArray["member"];
		$fk=OrmUtils::getFirstKey($annotationArray["className"]);
		$key=$annotationArray["className"]."|".$member."|".$fkField;
		if(!isset($ret[$key])){
			$ret[$key]=new PendingRelationsRequest();
		}
		$ret[$key]->addPartObject($instance,$fk . "= ?",$value);
	}
}
