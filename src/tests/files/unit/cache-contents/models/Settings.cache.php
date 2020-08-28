<?php
return array (
				"#tableName" => "Settings",
				"#primaryKeys" => array ("id" => "id" ),
				"#manyToOne" => array (),
				"#fieldNames" => array ("id" => "id","name" => "name","organizationsettingss" => "organizationsettingss" ),
				"#memberNames" => array ("id" => "id","name" => "name","organizationsettingss" => "organizationsettingss" ),
				"#fieldTypes" => array ("id" => "int(11)","name" => "varchar(45)","organizationsettingss" => "mixed" ),
				"#nullable" => array ("name" ),
				"#notSerializable" => array ("organizationsettingss" ),
				"#transformers" => array (),
				"#accessors" => array ("id" => "setId","name" => "setName","organizationsettingss" => "setOrganizationsettingss" ),
				"#oneToMany" => array ("organizationsettingss" => array ("mappedBy" => "settings","className" => "models\\Organizationsettings" ) ) );
