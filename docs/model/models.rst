ORM
===
.. note::
   if you want to automatically generate the models, consult the :doc:`generating models</model/generation>` part.

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
    * @table("name"=>"user")
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
.. note:: 
   **Naming convention** |br|
   Foreign key field names consist of the primary key name of the referenced table followed by the name of the referenced table whose first letter is capitalized. |br|
   **Example** |br|
   ``idUser`` for the table ``user`` whose primary key is ``id``


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

If the naming conventions are not respected for foreign keys, |br|
it is possible to specify the related fields.

.. code-block:: php
   :linenos:
   :caption: app/models/Group.php
   :emphasize-lines: 11-16
   
    namespace models;
    
    class Group{
    	/**
    	 * @id
    	**/
    	private $id;
    
    	private $name;

    	/**
    	 * @manyToMany("targetEntity"=>"models\\User","inversedBy"=>"groupes")
    	 * @joinTable("name"=>"groupeusers",
    	 * "joinColumns"=>["name"=>"id_groupe","referencedColumnName"=>"id"],
    	 * "inverseJoinColumns"=>["name"=>"id_user","referencedColumnName"=>"id"])
    	**/
    	private $users;

    }

ORM Annotations
---------------
Annotations for classes
^^^^^^^^^^^^^^^^^^^^^^^

+-------------+----------------------------------------------+------------+-----------------------+
| @annotation | role                                         | properties | role                  |
+=============+==============================================+============+=======================+
| @table      | Defines the associated table name.                                                |
+-------------+----------------------------------------------+------------+-----------------------+

Annotations for members
^^^^^^^^^^^^^^^^^^^^^^^

+-------------+----------------------------------------------+--------------+-----------------------------------+
| @annotation | role                                         | properties   | role                              |
+=============+==============================================+==============+===================================+
| @id         | Defines the primary key(s).                                                                     |
+-------------+----------------------------------------------+--------------+-----------------------------------+
| @column     | Specify the associated field caracteristics. | name         | Name of the associated field      |
+             +                                              +--------------+-----------------------------------+
|             |                                              | nullable     | true if value can be null         |
+             |                                              +--------------+-----------------------------------+
|             |                                              | dbType       | Type of the field in database     |
+-------------+----------------------------------------------+--------------+-----------------------------------+
| @transient  | Specify that the field is not persistent.                                                       |
+-------------+----------------------------------------------+--------------+-----------------------------------+

Associations
^^^^^^^^^^^^

+----------------------+----------------------------------------------+--------------------------+-------------------------------------------------------------+
| @annotation (extends)| role                                         | properties [optional]    | role                                                        |
+======================+==============================================+==========================+=============================================================+
| @manyToOne           | Defines a single-valued association to another entity class.                                                                          |
+----------------------+----------------------------------------------+--------------------------+-------------------------------------------------------------+
| @joinColumn (@column)| Indicates the foreign key in manyToOne asso. | className                | Class of the member                                         |
+                      +                                              +--------------------------+-------------------------------------------------------------+
|                      |                                              | [referencedColumnName]   | Name of the associated column                               |
+----------------------+----------------------------------------------+--------------------------+-------------------------------------------------------------+
| @oneToMany           | Defines a multi-valued association to        | className                | Class of the objects in member                              |
+                      + another entity class.                        +--------------------------+-------------------------------------------------------------+
|                      |                                              | [mappedBy]               | Name of the association-mapping                             |
|                      |                                              |                          | attribute on the owning side                                |
+----------------------+----------------------------------------------+--------------------------+-------------------------------------------------------------+
| @manyToMany          | Defines a many-valued association with       | targetEntity             | Class of the objects in member                              |
+                      + many-to-many multiplicity                    +--------------------------+-------------------------------------------------------------+
|                      |                                              | [inversedBy]             | Name of the association-member on the inverse-side          |
+                      |                                              +--------------------------+-------------------------------------------------------------+
|                      |                                              | [mappedBy]               | Name of the association-member on the owning side           |
+----------------------+----------------------------------------------+--------------------------+-------------------------------------------------------------+
| @joinTable           | Defines the association table for            | name                     | The name of the association table                           |
+                      + many-to-many multiplicity                    +--------------------------+-------------------------------------------------------------+
|                      |                                              | [joinColumns]            | @column => name and referencedColumnName for this side      |
+                      |                                              +--------------------------+-------------------------------------------------------------+
|                      |                                              | [inverseJoinColumns]     | @column => name and referencedColumnName for the other side |
+----------------------+----------------------------------------------+--------------------------+-------------------------------------------------------------+

.. |br| raw:: html

   <br />
