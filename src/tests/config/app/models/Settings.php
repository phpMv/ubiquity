<?php
namespace models;
class Settings{
	/**
	 * @id
	 * @column("name"=>"id","nullable"=>"","dbType"=>"int(11)")
	*/
	private $id;

	/**
	 * @column("name"=>"name","nullable"=>"","dbType"=>"varchar(45)")
	*/
	private $name;

	/**
	 * @oneToMany("mappedBy"=>"settings","className"=>"models\\Organizationsettings")
	*/
	private $organizationsettingss;

	 public function getId(){
		return $this->id;
	}

	 public function setId($id){
		$this->id=$id;
	}

	 public function getName(){
		return $this->name;
	}

	 public function setName($name){
		$this->name=$name;
	}

	 public function getOrganizationsettingss(){
		return $this->organizationsettingss;
	}

	 public function setOrganizationsettingss($organizationsettingss){
		$this->organizationsettingss=$organizationsettingss;
	}

	 public function __toString(){
		return $this->name;
	}

}