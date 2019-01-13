<?php

namespace Ubiquity\orm\traits;

use Ubiquity\orm\OrmUtils;
use Ubiquity\orm\parser\ManyToManyParser;
use Ubiquity\orm\parser\ConditionParser;
use Ubiquity\orm\parser\Reflexion;

/**
 * @author jc
 * @property \Ubiquity\db\Database $db
 */
trait DAORelationsTrait {
	abstract protected static function _getAll($className, ConditionParser $conditionParser, $included=true,$useCache=NULL);
	
	private static function generateManyToManyParser(ManyToManyParser $parser,&$myPkValues){
		$sql=$parser->generateConcatSQL();
		$result=self::$db->prepareAndFetchAll($sql,$parser->getWhereValues());
		$condition=$parser->getParserWhereMask(" ?");
		$cParser=new ConditionParser();		
		foreach ($result as $row){
			$values=explode(",", $row["_concat"]);
			$myPkValues[$row["_field"]]=$values;
			$cParser->addParts($condition, $values);
		}
		$cParser->compileParts();
		return $cParser;
	}
	
	private static function _getIncludedNext($included,$member){
		return (isset($included[$member]))?(is_bool($included[$member])?$included[$member]:[$included[$member]]):false;
	}
	
	private static function getManyToManyFromArrayIds($objectClass,$relationObjects, $ids){
		$ret=[];
		$prop=OrmUtils::getFirstPropKey($objectClass);
		foreach ( $relationObjects as $targetEntityInstance ) {
			$id=Reflexion::getPropValue($targetEntityInstance,$prop);
			if (array_search($id, $ids)!==false) {
				array_push($ret, $targetEntityInstance);
			}
		}
		return $ret;
	}
	
	protected static function getIncludedForStep($included){
		if(is_bool($included)){
			return $included;
		}
		$ret=[];
		if(is_array($included)){
			foreach ($included as &$includedMember){
				if(is_array($includedMember)){
					foreach ($includedMember as $iMember){
						self::parseEncludeMember($ret, $iMember);
					}
				}else{
					self::parseEncludeMember($ret, $includedMember);
				}
			}
		}
		return $ret;
	}
	
	private static function parseEncludeMember(&$ret,$includedMember){
		$array=explode(".", $includedMember);
		$member=array_shift($array);
		if(sizeof($array)>0){
			$newValue=implode(".", $array);
			if($newValue==='*'){
				$newValue=true;
			}
			if(isset($ret[$member])){
				if(!is_array($ret[$member])){
					$ret[$member]=[$ret[$member]];
				}
				$ret[$member][]=$newValue;
			}else{
				$ret[$member]=$newValue;
			}
		}else{
			if(isset($member) && ""!=$member){
				$ret[$member]=false;
			}else{
				return;
			}
		}
	}
	
	private static function getInvertedJoinColumns($included,&$invertedJoinColumns){
		foreach ($invertedJoinColumns as $column=>&$annot){
			$member=$annot["member"];
			if(isset($included[$member])===false){
				unset($invertedJoinColumns[$column]);
			}
		}
	}
	
	private static function getToManyFields($included,&$toManyFields){
		foreach ($toManyFields as $member=>$annotNotUsed){
			if(isset($included[$member])===false){
				unset($toManyFields[$member]);
			}
		}
	}
	
	protected static function _initRelationFields($included,$metaDatas,&$invertedJoinColumns,&$oneToManyFields,&$manyToManyFields){
		if (isset($metaDatas["#invertedJoinColumn"])){
			$invertedJoinColumns=$metaDatas["#invertedJoinColumn"];
		}
		if (isset($metaDatas["#oneToMany"])) {
			$oneToManyFields=$metaDatas["#oneToMany"];
		}
		if (isset($metaDatas["#manyToMany"])) {
			$manyToManyFields=$metaDatas["#manyToMany"];
		}
		if(is_array($included)){
			if(isset($invertedJoinColumns)){
				self::getInvertedJoinColumns($included, $invertedJoinColumns);
			}
			if(isset($oneToManyFields)){
				self::getToManyFields($included, $oneToManyFields);
			}
			if(isset($manyToManyFields)){
				self::getToManyFields($included, $manyToManyFields);
			}
		}
	}
}
