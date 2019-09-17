Session
=======
.. |br| raw:: html

   <br />

.. note:: For all Http features, Ubiquity uses technical classes containing static methods. 
          This is a design choice to avoid dependency injection that would degrade performances.

The **USession** class provides additional functionality to more easily manipulate native **$_SESSION** php array.

Starting the session
--------------------
The Http session is started automatically if the **sessionName** key is populated in the **app/config.php** configuration file:

.. code-block:: php
   
   <?php
   return array(
		...
		"sessionName"=>"key-for-app",
		...
    );

If the sessionName key is not populated, it is necessary to start the session explicitly to use it:

.. code-block:: php

    use Ubiquity\utils\http\USession;
    ...
    USession::start("key-for-app");
    

.. note:: The **name** parameter is optional but recommended to avoid conflicting variables.


Creating or editing a session variable
--------------------------------------

.. code-block:: php
   
   use Ubiquity\utils\http\USession;
   
   USession::set("name","SMITH");
   USession::set("activeUser",$user);
   
Retrieving data
--------------------
The **get** method returns the `null` value if the key **name** does not exist in the session variables.

.. code-block:: php
   
   use Ubiquity\utils\http\USession;
   
   $name=USession::get("name");
   
The **get** method can be called with the optional second parameter returning a value if the key does not exist in the session variables.

.. code-block:: php
   
   $name=USession::get("page",1);

.. note:: The **session** method is an alias of the **get** method.
    
The **getAll** method returns all session vars:

.. code-block:: php
   
   $sessionVars=USession::getAll();

Testing
-------
The **exists** method tests the existence of a variable in session.

.. code-block:: php
   
   if(USession::exists("name")){
   	//do something when name key exists in session
   }

The **isStarted** method checks the session start

.. code-block:: php
   
   if(USession::isStarted()){
   	//do something if the session is started
   }
     
Deleting variables
------------------
The **delete** method remove a session variable:

.. code-block:: php
   
   USession::delete("name");

Explicit closing of the session
-------------------------------
The **terminate** method closes the session correctly and deletes all session variables created:

.. code-block:: php
   
   USession::terminate();
