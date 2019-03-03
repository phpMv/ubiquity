<?php

namespace Ubiquity\controllers\rest\api\jsonapi;

use Ubiquity\controllers\rest\ResponseFormatter;
use Ubiquity\orm\OrmUtils;
use Ubiquity\cache\ClassUtils;

class JsonApiResponseFormatter extends ResponseFormatter {
	private $selfLink = "%baseRoute%/%classname%/%id%/";
	private $relationLink="%baseRoute%/%classname%/%id%/%member%/";
	private $baseRoute;
	
	public function __construct($baseRoute=""){
		$this->baseRoute=$baseRoute;
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\controllers\rest\ResponseFormatter::cleanRestObject()
	 */
	public function cleanRestObject($o) {
		$pk=OrmUtils::getFirstKeyValue ( $o );
		$classname=get_class ( $o );
		$frontClassname=$this->getFrontClassname($classname);
		$r = [ 'id' => $pk,'type' => $frontClassname ];
		$r ['attributes'] = $o->_rest;
		$fieldsInRelations=OrmUtils::getRelationInfos($classname);
		$this->addSelfLink($r, $pk, $frontClassname);
		$o=$o->_rest;
		foreach ( $o as $k => $v ) {
			if(isset($fieldsInRelations[$k])){
				if (isset ( $v->_rest )) {
					$r ['included'] [$k] = $v->_rest;
				}elseif (\is_array ( $v )) {
					foreach ( $v as $index => $value ) {
						if (isset ( $value->_rest ))
							$v [$index] = $this->cleanRestObject ( $value );
					}
					$r ['included'] [$k] = $v;
				}else{
					$member=$fieldsInRelations[$k]['member']??$k;
					$rFrontClassname=$this->getFrontClassname($fieldsInRelations[$k]['className']);
					$r['relationships'][$member]['data']=['id'=>$v,'type'=>$rFrontClassname];
					$this->addRelationshipsLink($r, $pk, $v, $frontClassname, $rFrontClassname, $member);
				}
				
				unset($r['attributes'][$k]);
			}
		}
		unset($r['attributes']['id']);
		unset($r['attributes']['_rest']);
		return $r;
	}
	
	protected function addSelfLink(&$r,$pk,$frontClassname){
		$r['links']['self']=$this->getLink($this->selfLink,["baseRoute"=>$this->baseRoute,'id'=>$pk,'classname'=>$frontClassname]);
	}
	
	protected function addRelationshipsLink(&$r,$pk,$pkMember,$frontClassname,$rFrontClassname,$member){
		$r['relationships'][$member]['links']=[
				$this->getLink($this->relationLink,["baseRoute"=>$this->baseRoute,'id'=>$pk,'member'=>$member,'classname'=>$frontClassname]),
				$this->getLink($this->selfLink,["baseRoute"=>$this->baseRoute,'id'=>$pkMember,'classname'=>$rFrontClassname])];
	}
	
	private function getLink($pattern,$params=[]){
		$r=$pattern;
		foreach ($params as $k=>$v){
			$r=str_replace('%'.$k.'%', $v, $r);
		}
		return $r;
	}
	
	private function getFrontClassname($classname){
		return lcfirst(ClassUtils::getClassSimpleName ( $classname ));
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\controllers\rest\ResponseFormatter::format()
	 */
	public function format($arrayResponse) {
		return parent::format ( $arrayResponse );
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\controllers\rest\ResponseFormatter::formatException()
	 */
	public function formatException($e) {
		return parent::formatException ( $e );
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\controllers\rest\ResponseFormatter::get()
	 */
	public function get($datas) {
		$datas = $this->getDatas ( $datas );
		return $this->format ( [ "data" => $datas] );
	}
	

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\controllers\rest\ResponseFormatter::getDatas()
	 */
	public function getDatas($datas) {
		return parent::getDatas ( $datas );
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\controllers\rest\ResponseFormatter::getJSONDatas()
	 */
	public function getJSONDatas($datas) {
		return parent::getJSONDatas ( $datas );
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\controllers\rest\ResponseFormatter::getModel()
	 */
	public function getModel($controllerName) {
		return parent::getModel ( $controllerName );
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\controllers\rest\ResponseFormatter::getOne()
	 */
	public function getOne($datas) {
		return parent::getOne ( $datas );
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\controllers\rest\ResponseFormatter::toJson()
	 */
	public function toJson($data) {
		return parent::toJson ( $data );
	}
}

