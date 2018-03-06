Request
=======
.. note:: For all Http features, Ubiquity uses technical classes containing static methods. 
          This is a design choice to avoid dependency injection that would degrade performance.

The **URequest** class provides additional functionality to more easily manipulate native **$_POST** and **$_GET** php arrays.

Retrieving data
--------------------
From the post method
^^^^^^^^^^^^^^^^^^^
.. code-block:: php
   
   use Ubiquity\utils\http\URequest;
   
   $name=URequest::post("name");
