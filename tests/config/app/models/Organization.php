<?php
namespace models;
class Organization{
	/**
	 * @id
	 * @column("name"=>"id","nullable"=>"","dbType"=>"int(11)")
	*/
	private $id;

	/**
	 * @column("name"=>"name","nullable"=>"","dbType"=>"varchar(100)")
	*/
	private $name;

	/**
	 * @column("name"=>"domain","nullable"=>"","dbType"=>"varchar(255)")
	*/
	private $domain;

	/**
	 * @column("name"=>"aliases","nullable"=>1,"dbType"=>"text")
	*/
	private $aliases;

	/**
	 * @oneToMany("mappedBy"=>"organization","className"=>"models\\Groupe")
	*/
	private $groupes;

	/**
	 * @oneToMany("mappedBy"=>"organization","className"=>"models\\Organizationsettings")
	*/
	private $organizationsettingss;

	/**
	 * @oneToMany("mappedBy"=>"organization","className"=>"models\\User")
	*/
	private $users;

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

	 public function getDomain(){
		return $this->domain;
	}

	 public function setDomain($domain){
		$this->domain=$domain;
	}

	 public function getAliases(){
		return $this->aliases;
	}

	 public function setAliases($aliases){
		$this->aliases=$aliases;
	}

	 public function getGroupes(){
		return $this->groupes;
	}

	 public function setGroupes($groupes){
		$this->groupes=$groupes;
	}

	 public function getOrganizationsettingss(){
		return $this->organizationsettingss;
	}

	 public function setOrganizationsettingss($organizationsettingss){
		$this->organizationsettingss=$organizationsettingss;
	}

	 public function getUsers(){
		return $this->users;
	}

	 public function setUsers($users){
		$this->users=$users;
	}

	 public function __toString(){
		return $this->domain;
	}

}