<?php
return array (
				"#tableName" => "User",
				"#primaryKeys" => array ("id" => "id" ),
				"#manyToOne" => array ("organization" ),
				"#fieldNames" => array ("id" => "id","firstname" => "firstname","lastname" => "lastname","email" => "email","password" => "password","suspended" => "suspended","organization" => "idOrganization","connections" => "connections","groupes" => "groupes" ),
				"#memberNames" => array ("id" => "id","firstname" => "prenom","lastname" => "lastname","email" => "email","password" => "password","suspended" => "suspended","idOrganization" => "organization","groupes" => "groupes" ),
				"#fieldTypes" => array ("id" => "int(11)","firstname" => "varchar(65)","lastname" => "varchar(65)","email" => "varchar(255)","password" => "varchar(255)","suspended" => "tinyint(1)","organization" => false,"connections" => "mixed","groupes" => "mixed" ),
				"#nullable" => array ("password","suspended" ),
				"#notSerializable" => array ("organization","connections","groupes" ),
				"#transformers" => array ("toView" => array ("password" => "transf\\Sha1" ) ),
				"#accessors" => array ("id" => "setId","firstname" => "setFirstname","lastname" => "setLastname","email" => "setEmail","password" => "setPassword","suspended" => "setSuspended","idOrganization" => "setOrganization","connections" => "setConnections","groupes" => "setGroupes" ),
				"#oneToMany" => array ("connections" => array ("mappedBy" => "user","className" => "models\\Connection" ) ),
				"#manyToMany" => array ("groupes" => array ("targetEntity" => "models\\Groupe","inversedBy" => "users" ) ),
				"#joinTable" => array ("groupes" => array ("name" => "groupeusers" ) ),
				"#joinColumn" => array ("organization" => array ("className" => "models\\Organization","name" => "idOrganization","nullable" => false ) ),
				"#invertedJoinColumn" => array ("idOrganization" => array ("member" => "organization","className" => "models\\Organization" ) ) );
