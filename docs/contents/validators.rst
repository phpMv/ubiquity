Validators
==========

.. note::
   The Validators module uses the static class **ValidatorsManager** to manage validation.
   

Validators are used to check that the member datas of an object complies with certain constraints.

Adding validators
-----------------

Either the **Author** class that we want to use in our application :

.. code-block:: php
   :linenos:
   :caption: app/models/Author.php
   :emphasize-lines: 6
   
	namespace models;
	
	class Author {
		/**
		 * @var string
		 * @validator("notEmpty")
		 */
		private $name;
		
		public function getName(){
			return $this->name;
		}
		
		public function setName($name){
			$this->name=$name;
		}
	}

We added a validation constraint on the **name** member with the **@validator** annotation, so that it is not empty.

Generating cache
----------------
Run this command in console mode to create the cache data of the **Author** class :

.. code-block:: php
   
   Ubiquity init-cache -t=models

Validator cache is generated in ``app/cache/contents/validators/models/Author.cache.php``.

Validating instances
--------------------
an instance
~~~~~~~~~~~

.. code-block:: php
   
   
	public function testValidateAuthor(){
		$author=new Author();
		//Do something with $author
		$violations=ValidatorsManager::validate($author);
		if(sizeof($violations)>0){
			echo implode('<br>', ValidatorsManager::validate($author));
		}else{
			echo 'The author is valid!';
		}
	}


if the **name** of the author is empty, this action should display:

.. code-block:: bash
   
   name : This value should not be empty

The **validate** method returns an array of **ConstraintViolation** instances.

multiple instances
~~~~~~~~~~~~~~~~~~

.. code-block:: php
   
   
	public function testValidateAuthors(){
		$authors=DAO::getAll(Author::class);
		$violations=ValidatorsManager::validateInstances($author);
		foreach($violations as $violation){
			echo $violation.'<br>';
		}
	}

Models generation with default validators
-----------------------------------------

When classes are automatically generated from the database, default validators are associated with members, based on the fields' metadatas.

.. code-block:: php
   
   Ubiquity create-model User

.. code-block:: php
   :linenos:
   :caption: app/models/Author.php
   
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
	}

These validators can then be modified. |br|
Modifications must always be folowed by a re-initialization of the model cache.

.. code-block:: php
   
   Ubiquity init-cache -t=models

Models validation informations can be displayed with devtools :

.. code-block:: php
   
   Ubiquity info:validation -m=User

.. image:: /_static/images/validation/info-validation-devtools.png
   :class: console


Gets validators on email field:

.. code-block:: php
   
   Ubiquity info:validation email -m=User

.. image:: /_static/images/validation/info-validation-email-devtools.png
   :class: console

Validation informations are also accessible from the **models** part of the webtools:

.. image:: /_static/images/validation/info-validation-webtools.png
   :class: bordered

Validator types
---------------
Basic
~~~~~
+-------------+------------------------------------------+-----------------------+------------------------+
|Validator    |Roles                                     |Constraints            |Accepted values         |
+=============+==========================================+=======================+========================+
|isBool       |Check if value is a boolean               |                       |true,false,0,1          |
+-------------+------------------------------------------+-----------------------+------------------------+
|isEmpty      |Check if value is empty                   |                       |'',null                 |
+-------------+------------------------------------------+-----------------------+------------------------+
|isFalse      |Check if value is false                   |                       |false,'false',0,'0'     |
+-------------+------------------------------------------+-----------------------+------------------------+
|isNull       |Check if value is null                    |                       |null                    |
+-------------+------------------------------------------+-----------------------+------------------------+
|isTrue       |Check if value is true                    |                       |true,'true',1,'1'       |
+-------------+------------------------------------------+-----------------------+------------------------+
|notEmpty     |Check if value is not empty               |                       |!null && !''            |
+-------------+------------------------------------------+-----------------------+------------------------+
|notNull      |Check if value is not null                |                       |!null                   |
+-------------+------------------------------------------+-----------------------+------------------------+
|type         |Check if value is of type {type}          |{type}                 |                        |
+-------------+------------------------------------------+-----------------------+------------------------+

Comparison
~~~~~~~~~~

Dates
~~~~~

Multiples
~~~~~~~~~

Strings
~~~~~~~


.. |br| raw:: html

   <br />
