.. _db-console:
Generate models from Database with devtools
===========================================

In this part, your project is already created. |br|
If you do not have a mysql database on hand, you can download this one: :download:`messagerie.sql </model/messagerie.sql>`

Configuration
-------------

Check the database configuration in **app/config/config.php**

.. code-block:: bash
   
   Ubiquity config database

.. image:: /_static/images/model/check-config.png
   :class: console
  
Change the configuration of the database to use the **messagerie** database:

.. code-block:: bash
   
   Ubiquity config:set --database.dbName=messagerie

.. image:: /_static/images/model/update-dbName.png
   :class: console

Generation
----------
To generate all models, use the **all-models** command:

.. code-block:: bash
   
   Ubiquity all-models

.. image:: /_static/images/model/db-created.png
   :class: console

Checking
--------

Models meta-datas
^^^^^^^^^^^^^^^^^
To obtain the metadatas of all created models:

.. code-block:: bash
   
   Ubiquity info:models

For a precise model:

.. code-block:: bash
   
   Ubiquity info:models -m=Groupe

.. image:: /_static/images/model/model-metas.png
   :class: console

Models validation info
^^^^^^^^^^^^^^^^^^^^^^
To obtain the validation rules for the model **User**:

.. code-block:: bash
   
   Ubiquity info:validation -m=User

.. image:: /_static/images/model/model-validation-user.png
   :class: console

On a particular member:

.. code-block:: bash
   
   Ubiquity info:validation -m=User -f=email

.. image:: /_static/images/model/model-validation-user-email.png
   :class: console
   
Generated classes
^^^^^^^^^^^^^^^^^
The **User** class:
.. code-block:: php
   :linenos:
   :caption: app/models/User.php
   
   namespace models;
	class User{
		/**
		 * @id
		 * @column("name"=>"id","nullable"=>false,"dbType"=>"int(11)")
		 * @validator("id","constraints"=>array("autoinc"=>true))
		**/
		private $id;
	
		/**
		 * @column("name"=>"firstname","nullable"=>false,"dbType"=>"varchar(65)")
		 * @validator("length","constraints"=>array("max"=>65,"notNull"=>true))
		**/
		private $firstname;
	
		/**
		 * @column("name"=>"lastname","nullable"=>false,"dbType"=>"varchar(65)")
		 * @validator("length","constraints"=>array("max"=>65,"notNull"=>true))
		**/
		private $lastname;
	
		/**
		 * @column("name"=>"email","nullable"=>false,"dbType"=>"varchar(255)")
		 * @validator("email","constraints"=>array("notNull"=>true))
		 * @validator("length","constraints"=>array("max"=>255))
		**/
		private $email;
	
		/**
		 * @column("name"=>"password","nullable"=>true,"dbType"=>"varchar(255)")
		 * @validator("length","constraints"=>array("max"=>255))
		**/
		private $password;
	
		/**
		 * @column("name"=>"suspended","nullable"=>true,"dbType"=>"tinyint(1)")
		 * @validator("isBool")
		**/
		private $suspended;
	
		/**
		 * @manyToOne
		 * @joinColumn("className"=>"models\\Organization","name"=>"idOrganization","nullable"=>false)
		**/
		private $organization;
	
		/**
		 * @oneToMany("mappedBy"=>"user","className"=>"models\\Connection")
		**/
		private $connections;
	
		/**
		 * @manyToMany("targetEntity"=>"models\\Groupe","inversedBy"=>"users")
		 * @joinTable("name"=>"groupeusers")
		**/
		private $groupes;
	}

