Validators
==========

Validators are used to check that the member data of an object complies with certain constraints.

.. note::
   The Validators module uses the static class **ValidatorsManager** to manage validation.
   
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

Run this command in console mode to create the cache data of the **Author** class :

.. code-block:: php
   
   Ubiquity init-cache



.. |br| raw:: html

   <br />
