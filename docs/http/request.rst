Request
=======
.. |br| raw:: html

   <br />

.. note:: For all Http features, Ubiquity uses technical classes containing static methods. 
          This is a design choice to avoid dependency injection that would degrade performances.

The **URequest** class provides additional functionality to more easily manipulate native **$_POST** and **$_GET** php arrays.

Retrieving data
--------------------
From the get method
^^^^^^^^^^^^^^^^^^^
The **get** method returns the `null` value if the key **name** does not exist in the get variables.

.. code-block:: php
   
   use Ubiquity\utils\http\URequest;
   
   $name=URequest::get("name");

The **get** method can be called with the optional second parameter returning a value if the key does not exist in the get variables.

.. code-block:: php
   
   $name=URequest::get("page",1);

From the post method
^^^^^^^^^^^^^^^^^^^
The **post** method returns the `null` value if the key **name** does not exist in the post variables.

.. code-block:: php
   
   use Ubiquity\utils\http\URequest;
   
   $name=URequest::post("name");

The **post** method can be called with the optional second parameter returning a value if the key does not exist in the post variables.

.. code-block:: php
   
   $name=URequest::post("page",1);

The **getPost** method applies a callback to the elements of the $_POST array and return them (default callback : **htmlEntities**) :

.. code-block:: php
   
   $protectedValues=URequest::getPost();

Retrieving and assigning multiple data
--------------------------------------------------
It is common to assign the values of an associative array to the members of an object. |br| 
This is the case for example when validating an object modification form.

The **setValuesToObject** method performs this operation :

Consider a **User** class:

.. code-block:: php
   
   class User {
   	private $firstname;
   	private $lastname;
   	
   	public function setFirstname($firstname){
   		$this->firstname=$firstname;
   	}
   	public function getFirstname(){
   		return $this->firstname;
   	}
   	
   	public function setLastname($lastname){
   		$this->lastname=$lastname;
   	}
   	public function getLastname(){
   		return $this->lastname;
   	}
   }
Consider a form to modify a user:

.. code-block:: html
   
   <form method="post" action="User/update">
   	<label for="firstname">Firstname:</label>
   	<input type="text" id="firstname" name="firstname" value="{{user.firstname}}">
   	<label for="lastname">Lastname:</label>
   	<input type="text" id="lastname" name="lastname" value="{{user.lastname}}">
   	<input type="submit" value="validate modifications">
   </form>

