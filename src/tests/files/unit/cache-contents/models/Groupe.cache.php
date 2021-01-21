<?php
return array (
				"#tableName" => "Groupe",
				"#primaryKeys" => array ("id" => "id" ),
				"#manyToOne" => array ("organization" ),
				"#fieldNames" => array ("id" => "id","name" => "name","email" => "email","aliases" => "aliases","organization" => "idOrganization","users" => "users" ),
				"#memberNames" => array ("id" => "id","name" => "name","email" => "email","aliases" => "aliases","idOrganization" => "organization","users" => "users" ),
				"#fieldTypes" => array ("id" => "int(11)","name" => "varchar(65)","email" => "varchar(255)","aliases" => "mediumtext","organization" => false,"users" => "mixed" ),
				"#nullable" => array ("name","email","aliases" ),
				"#notSerializable" => array ("organization","users" ),
		"#transformers" => array ("toView" => array ("id" => "Ubiquity\\contents\\transformation\\transformers\\Md5","name" => "Ubiquity\\contents\\transformation\\transformers\\FirstUpperCase","email" => "Ubiquity\\contents\\transformation\\transformers\\LowerCase","aliases" => "Ubiquity\\contents\\transformation\\transformers\\UpperCase" ) ),
				"#accessors" => array ("id" => "setId","name" => "setName","email" => "setEmail","aliases" => "setAliases","idOrganization" => "setOrganization","users" => "setUsers" ),
				"#manyToMany" => array ("users" => array ("targetEntity" => "models\\User","inversedBy" => "groupes" ) ),
				"#joinTable" => array ("users" => array ("name" => "groupeusers" ) ),
				"#joinColumn" => array ("organization" => array ("className" => "models\\Organization","name" => "idOrganization","nullable" => false ) ),
				"#invertedJoinColumn" => array ("idOrganization" => array ("member" => "organization","className" => "models\\Organization" ) ) );
