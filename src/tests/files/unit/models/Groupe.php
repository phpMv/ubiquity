<?php
namespace models;
class Groupe{
	/**
	 * @id
	 * @column("name"=>"id","nullable"=>"","dbType"=>"int(11)")
	*/
	private $id;

	/**
	 * @column("name"=>"name","nullable"=>"","dbType"=>"varchar(65)")
	*/
	private $name;

	/**
	 * @column("name"=>"email","nullable"=>1,"dbType"=>"varchar(255)")
	*/
	private $email;

	/**
	 * @column("name"=>"aliases","nullable"=>1,"dbType"=>"mediumtext")
	*/
	private $aliases;

	/**
	 * @manyToOne
	 * @joinColumn("className"=>"models\\Organization","name"=>"idOrganization","nullable"=>"")
	*/
	private $organization;

	/**
	 * @manyToMany("targetEntity"=>"models\\User","inversedBy"=>"groupes")
	 * @joinTable("name"=>"groupeusers","joinColumns"=>["name"=>"idUser","referencedColumnName"=>"id"],"inverseJoinColumns"=>["name"=>"idGroupe","referencedColumnName"=>"id"])
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

	 public function getEmail(){
		return $this->email;
	}

	 public function setEmail($email){
		$this->email=$email;
	}

	 public function getAliases(){
		return $this->aliases;
	}

	 public function setAliases($aliases){
		$this->aliases=$aliases;
	}

	 public function getOrganization(){
		return $this->organization;
	}

	 public function setOrganization($organization){
		$this->organization=$organization;
	}

	 public function getUsers(){
		return $this->users;
	}

	 public function setUsers($users){
		$this->users=$users;
	}

	 public function __toString(){
		return $this->name;
	}

}