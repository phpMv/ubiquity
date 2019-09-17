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
   
   $name=URequest::get("name",1);

From the post method
^^^^^^^^^^^^^^^^^^^
The **post** method returns the `null` value if the key **name** does not exist in the post variables.

.. code-block:: php
   
   use Ubiquity\utils\http\URequest;
   
   $name=URequest::post("name");

The **post** method can be called with the optional second parameter returning a value if the key does not exist in the post variables.

.. code-block:: php
   
   $name=URequest::post("name",1);

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
    	private $id;
   	private $firstname;
   	private $lastname;
   	
   	public function setId($id){
   		$this->id=$id;
   	}
   	public function getId(){
   		return $this->id;
   	}
   	
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
   
   <form method="post" action="Users/update">
    <input type="hidden" name="id" value="{{user.id}}">
   	<label for="firstname">Firstname:</label>
   	<input type="text" id="firstname" name="firstname" value="{{user.firstname}}">
   	<label for="lastname">Lastname:</label>
   	<input type="text" id="lastname" name="lastname" value="{{user.lastname}}">
   	<input type="submit" value="validate modifications">
   </form>

The **update** action of the **Users** controller must update the user instance from POST values. |br|
Using the **setPostValuesToObject** method avoids the assignment of variables posted one by one to the members of the object. |br|
It is also possible to use **setGetValuesToObject** for the **get** method, or **setValuesToObject** to assign the values of any associative array to an object.

.. code-block:: php
   :linenos:
   :caption: app/controllers/Users.php
   :emphasize-lines: 10
      
    namespace controllers;
    
    use Ubiquity\orm\DAO;
    use Uniquity\utils\http\URequest;
    
    class Users extends BaseController{
    	...
    	public function update(){
    		$user=DAO::getOne("models\User",URequest::post("id"));
    		URequest::setPostValuesToObject($user);
    		DAO::update($user);
    	}
    }
    

.. note:: **SetValuesToObject** methods use setters to modify the members of an object. 
          The class concerned must therefore implement setters for all modifiable members.

Testing the request
-------------------

isPost
^^^^^^

The **isPost** method returns `true` if the request was submitted via the POST method: |br|
In the case below, the `initialize` method only loads the `vHeader.html` view if the request is not an Ajax request.

.. code-block:: php
   :linenos:
   :caption: app/controllers/Users.php
   :emphasize-lines: 9
      
    namespace controllers;
    
    use Ubiquity\orm\DAO;
    use Uniquity\utils\http\URequest;
    
    class Users extends BaseController{
    	...
    	public function update(){
    		if(URequest::isPost()){
    			$user=DAO::getOne("models\User",URequest::post("id"));
    			URequest::setPostValuesToObject($user);
    			DAO::update($user);
    		}
    	}
    }


isAjax
^^^^^^
The **isAjax** method returns `true` if the query is an Ajax query:

.. code-block:: php
   :linenos:
   :caption: app/controllers/Users.php
   :emphasize-lines: 3
      
    ...
	public function initialize(){
		if(!URequest::isAjax()){
			$this->loadView("main/vHeader.html");
		}
	}
	...
      
      
isCrossSite
^^^^^^^^^^^
The **isCrossSite** method verifies that the query is not cross-site.

