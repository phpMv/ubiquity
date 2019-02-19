ORM
===
.. info::
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
   :caption: app/models/Product.php
   
    namespace models;
    class Product{
    	/**
    	 * @id
    	**/
    	private $id;
    
    	private $name;
    
    	public function getName(){
    		return $this->name;
    	}
    	public function setName($name){
    		$this->name=$name;
    	}
    }

Mapping
^^^^^^^
Table->Class
++++++++++++
If the name of the table is different from the name of the class, the annotation **@table** allows to specify the name of the table.

.. code-block:: php
   :linenos:
   :caption: app/models/Product.php
   :emphasize-lines: 3-5
   
    namespace models;
    
    /**
    * @table("product")
    **/
    class Product{
    	/**
    	 * @id
    	*/
    	private $id;
    
    	private $name;
    
    	public function getName(){
    		return $this->name;
    	}
    	public function setName($name){
    		$this->name=$name;
    	}
    }

Field->Member
+++++++++++++
If the name of a field is different from the name of a member in the class, the annotation **@column** allows to specify a different field name.

.. code-block:: php
   :linenos:
   :caption: app/models/Product.php
   :emphasize-lines: 12-14
   
    namespace models;
    
    /**
    * @table("product")
    **/
    class Product{
    	/**
    	 * @id
    	**/
    	private $id;
    
    	/**
    	* column("product_name")
    	**/
    	private $name;
    
    	public function getName(){
    		return $this->name;
    	}
    	public function setName($name){
    		$this->name=$name;
    	}
    }

//TODO

.. |br| raw:: html

   <br />