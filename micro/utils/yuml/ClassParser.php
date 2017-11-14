<?php

namespace micro\utils\yuml;

use micro\orm\OrmUtils;

/**
 * @author jc
 * yuml export tool for class
 */
class ClassParser {
	protected $class;
	protected $displayProperties=true;
	protected $displayMethods=false;
	protected $displayMethodsParams=false;
	protected $displayPropertiesTypes=false;
	protected $displayAssociations;
	protected $displayAssociationClassProperties=false;
	protected $properties;
	protected $oneToManys=[];
	protected $manyToOne=[];

	public function __construct($class,$displayProperties=true,$displayAssociations=true,$displayMethods=false,$displayMethodsParams=false,$displayPropertiesTypes=false,$displayAssociationClassProperties=false){
		$this->class=$class;
		$this->displayProperties=$displayProperties;
		$this->displayAssociations=$displayAssociations;
		$this->displayMethods=$displayMethods;
		$this->displayMethodsParams=$displayMethodsParams;
		$this->displayPropertiesTypes=$displayPropertiesTypes;
		$this->displayAssociationClassProperties=$displayAssociationClassProperties;
	}

	public function init($hasManyToOne,$hasOneToMany){
		if($hasManyToOne){
			$this->loadManyToOne();
		}
		if($hasOneToMany){
			$this->loadOneToManys();
		}
	}

	public function parse(){
		$reflect=new \ReflectionClass($this->class);
		$parts=[$reflect->getShortName()];

		if($this->displayProperties){
			$prikeys=OrmUtils::getKeyFields($this->class);
			$types=OrmUtils::getFieldTypes($this->class);
			$propertiesArray=[];
			$properties=$reflect->getProperties();
			foreach ($properties as $property){
				$propertyName=$property->getName();
				$type="";$isPri="";
				if($this->displayPropertiesTypes){
					if(\array_key_exists($propertyName, $types)){
						$type=Yuml::$parameterTypeSeparator.$types[$propertyName];
					}
				}
				if(\array_search($propertyName, $prikeys)!==false){
					$isPri=Yuml::$primary;
				}
				$propertiesArray[]=Yuml::setPropertyVariables([$this->getAccess($property),$isPri,$propertyName,$type]);
			}
			$parts[]=\implode(Yuml::$memberSeparator, $propertiesArray);
		}

		if($this->displayMethods){
			$methodsArray=[];
			$methods=$reflect->getMethods();
			foreach ($methods as $method){
				$parameters="";
				if($this->displayMethodsParams){
					$parameters=$this->getMethodParameters($method);
				}
				$methodName=$method->getName();
				$type="";
				if($method->hasReturnType()){
					$type=Yuml::$parameterTypeSeparator.$method->getReturnType();
				}
				$methodsArray[]=Yuml::setMethodVariables([$this->getAccess($method),$methodName,$parameters,$type]);
			}
			$parts[]=\implode(Yuml::$memberSeparator, $methodsArray);
		}

		$result=\implode(Yuml::$classSeparator, $parts);
		$result=Yuml::setClassContent($result);
		return $result;
	}

	protected function getShortClassName($class){
		$reflect=new \ReflectionClass($class);
		return $reflect->getShortName();
	}

	protected function loadOneToManys(){
		$oneToManys=OrmUtils::getAnnotationInfo($this->class, "#oneToMany");
		if($oneToManys){
			foreach ($oneToManys as $member=>$array){
				$this->oneToManys[$member]=$array["className"];
			}
		}
	}

	protected function loadManyToOne(){
		$manyToOne=OrmUtils::getAnnotationInfo($this->class, "#manyToOne");
		if($manyToOne){
			foreach ($manyToOne as $member){
				$joinColumn=OrmUtils::getAnnotationInfoMember($this->class, "#joinColumn", $member);
				if($joinColumn){
					if(isset($joinColumn["className"]))
						$this->manyToOne[$member]=$joinColumn["className"];
				}
			}
		}
	}

	protected function _getYumlManyToOne(){
		return $this->_getYumlRelationsType($this->manyToOne,"0..*-1");
	}

	protected function _getYumlOneToMany(){
		return $this->_getYumlRelationsType($this->oneToManys,"1-0..*");
	}

	protected function _getYumlRelationsType($relations,$branche){
		$myClass=$this->getShortClassName($this->class);
		$yumlRelations=[];
		foreach ($relations as $model){
			$yumlRelations[]=Yuml::setClassContent($myClass).$branche.new ClassParser($model,$this->displayAssociationClassProperties,false);
		}
		return $yumlRelations;
	}

	protected function getMethodParameters(\ReflectionMethod $method){
		$paramsValues=[];
		$parameters=$method->getParameters();
		foreach ($parameters as $parameter){
			$v=$parameter->getName();
			if($parameter->hasType()){
				$v.=Yuml::$parameterTypeSeparator.$parameter->getType();
			}
			$paramsValues[]=$v;
		}
		return \implode(Yuml::$parameterSeparator, $paramsValues);
	}

	protected function getAccess($property){
		$result=Yuml::$private;
		if($property->isPublic()){
			$result=Yuml::$public;
		}elseif($property->isProtected()){
			$result=Yuml::$protected;
		}
		return $result;
	}

	public function manyToOneTostring(){
		$this->loadManyToOne();
		return \implode(Yuml::$groupeSeparator, $this->_getYumlManyToOne());
	}

	public function oneToManyTostring(){
		$this->loadOneToManys();
		return \implode(Yuml::$groupeSeparator, $this->_getYumlOneToMany());
	}

	public function __toString(){
		$result=[$this->parse()];
		if($this->displayAssociations){
			$result=\array_merge($result,$this->_getYumlManyToOne());
			$result=\array_merge($result,$this->_getYumlOneToMany());
		}
		return \implode(Yuml::$groupeSeparator, $result);
	}


	public function setDisplayProperties($displayProperties) {
		$this->displayProperties=$displayProperties;
		return $this;
	}

	public function setDisplayMethods($displayMethods) {
		$this->displayMethods=$displayMethods;
		return $this;
	}

	public function setDisplayAssociations($displayAssociations) {
		$this->displayAssociations=$displayAssociations;
		return $this;
	}

}