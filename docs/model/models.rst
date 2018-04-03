ORM
===

.. |br| raw:: html

   <br />

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

//TODO