ORM
===
.. note::
   if you want to automatically generate the models, consult the :ref:`generating models<models/generation>` part.

A model class is just a plain old php object without inheritance. |br|
Models are located by default in the **app\\models** folder. |br|
Object Relational Mapping (ORM) relies on member annotations in the model class.

Models definition
-----------------
A basic model
^^^^^^^^^^^^^
- A model must define its primary key using the **@id** annotation on the members concerned
- Serialized members must have getters and setters
- Without any other annotation, a class corresponds to a table with the same name in the database, each member corresponds to a field of this table

.. code-block:: php
   :linenos:
   :caption: app/models/User.php
   
    namespace models;
    class User{
    	/**
    	 * @id
    	**/
    	private $id;
    
    	private $firstname;
    
    	public function getFirstname(){
    		return $this->firstname;
    	}
    	public function setFirstname($firstname){
    		$this->firstname=$firstname;
    	}
    }

Mapping
^^^^^^^
Table->Class
++++++++++++
If the name of the table is different from the name of the class, the annotation **@table** allows to specify the name of the table.

.. code-block:: php
   :linenos:
   :caption: app/models/User.php
   :emphasize-lines: 3-5
   
    namespace models;
    
    /**
    * @table("user")
    **/
    class User{
    	/**
    	 * @id
    	**/
    	private $id;
    
    	private $firstname;
    
    	public function getFirstname(){
    		return $this->firstname;
    	}
    	public function setFirstname($firstname){
    		$this->firstname=$firstname;
    	}
    }

Field->Member
+++++++++++++
If the name of a field is different from the name of a member in the class, the annotation **@column** allows to specify a different field name.

.. code-block:: php
   :linenos:
   :caption: app/models/User.php
   :emphasize-lines: 12-14
   
    namespace models;
    
    /**
    * @table("user")
    **/
    class User{
    	/**
    	 * @id
    	**/
    	private $id;
    
    	/**
    	* column("user_name")
    	**/
    	private $firstname;
    
    	public function getFirstname(){
    		return $this->firstname;
    	}
    	public function setFirstname($firstname){
    		$this->firstname=$firstname;
    	}
    }

Associations
^^^^^^^^^^^^

ManyToOne
+++++++++
A **user** belongs to an **organization**:

.. image:: /_static/images/model/manyToOne.png
   :class: bordered

.. code-block:: php
   :linenos:
   :caption: app/models/User.php
   :emphasize-lines: 11-13
   
    namespace models;
    
    class User{
    	/**
    	 * @id
    	**/
    	private $id;
    
    	private $firstname;

		/**
		 * @manyToOne
		 * @joinColumn("className"=>"models\\Organization","name"=>"idOrganization","nullable"=>false)
		**/
		private $organization;
	    
		public function getOrganization(){
			return $this->organization;
		}
	
		 public function setOrganization($organization){
			$this->organization=$organization;
		}
    }

The **@joinColumn** annotation specifies that:

- The member **$organization** is an instance of **models\Organization**
- The table **user** has a foreign key **idOrganization** refering to organization primary key
- This foreign key is not null => a user will always have an organization 

OneToMany
+++++++++
An **organization** has many **users**:

.. image:: /_static/images/model/oneToMany.png
   :class: bordered

.. code-block:: php
   :linenos:
   :caption: app/models/Organization.php
   :emphasize-lines: 11-13
   
	namespace models;
	
	class Organization{
		/**
		 * @id
		**/
		private $id;
	
		private $name;
	
		/**
		 * @oneToMany("mappedBy"=>"organization","className"=>"models\\User")
		**/
		private $users;
	}

In this case, the association is bi-directional. |br|
The **@oneToMany** annotation must just specify:

- The class of each user in users array : **models\User**
- the value of **@mappedBy** is the name of the association-mapping attribute on the owning side : **$organization** in **User** class 

ManyToMany
++++++++++
- A **user** can belong to **groups**. |br|
- A **group** consists of multiple **users**.

.. image:: /_static/images/model/manyToMany.png
   :class: bordered

.. code-block:: php
   :linenos:
   :caption: app/models/User.php
   :emphasize-lines: 11-13
   
    namespace models;
    
    class User{
    	/**
    	 * @id
    	**/
    	private $id;
    
    	private $firstname;

		/**
		 * @manyToMany("targetEntity"=>"models\\Group","inversedBy"=>"users")
		 * @joinTable("name"=>"groupusers")
		**/
		private $groups;

    }


.. code-block:: php
   :linenos:
   :caption: app/models/Group.php
   :emphasize-lines: 11-13
   
    namespace models;
    
    class Group{
    	/**
    	 * @id
    	**/
    	private $id;
    
    	private $name;

		/**
		 * @manyToMany("targetEntity"=>"models\\User","inversedBy"=>"groups")
		 * @joinTable("name"=>"groupusers")
		**/
		private $users;

    }


.. |br| raw:: html

   <br />