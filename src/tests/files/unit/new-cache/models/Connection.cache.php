<?php
return array (
				"#tableName" => "Connection",
				"#primaryKeys" => array ("id" => "id" ),
				"#manyToOne" => array ("user" ),
				"#fieldNames" => array ("id" => "id","dateCo" => "dateCo","url" => "url","user" => "idUser" ),
				"#memberNames" => array ("id" => "id","dateCo" => "dateCo","url" => "url","idUser" => "user" ),
				"#fieldTypes" => array ("id" => "int(11)","dateCo" => "datetime","url" => "varchar(255)","user" => false ),
				"#nullable" => array (),
				"#notSerializable" => array ("user" ),
				"#transformers" => array ("transform" => array ("dateCo" => "Ubiquity\\contents\\transformation\\transformers\\DateTime" ),"toView" => array ("dateCo" => "Ubiquity\\contents\\transformation\\transformers\\DateTime" ),"toForm" => array ("dateCo" => "Ubiquity\\contents\\transformation\\transformers\\DateTime" ) ),
				"#accessors" => array ("id" => "setId","dateCo" => "setDateCo","url" => "setUrl","idUser" => "setUser" ),
				"#joinColumn" => array ("user" => array ("className" => "models\\User","name" => "idUser","nullable" => false ) ),
				"#invertedJoinColumn" => array ("idUser" => array ("member" => "user","className" => "models\\User" ) ) );
