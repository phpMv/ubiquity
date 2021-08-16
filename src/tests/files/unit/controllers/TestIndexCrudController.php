<?php
namespace controllers;


/**
 * @route("path"=>"/crud/{resource}/","inherited"=>true,"automated"=>true)
 */
class TestIndexCrudController extends \Ubiquity\controllers\crud\MultiResourceCRUDController{

	/**
	 * @route("name"=>"crud.index","inherited"=>false,"priority"=>-1)
	 */
	public function index() {
		parent::index();
	}


	/**
	 * @route("path"=>"#/crud/home","name"=>"crud.home","priority"=>100)
	 */
	public function home(){
		parent::home();
	}

	protected function getIndexType():array {
		return ['four link cards','card'];
	}
	
	public function _getBaseRoute():string {
		return "/crud/".$this->resource;
	}
	

}
