.. _di:
Dependency injection
====================

.. note::
   For performance reasons, dependency injection is not used in the core part of the framework.
   
Dependency Injection (DI) is a design pattern used to implement IoC. |br|
It allows the creation of dependent objects outside of a class and provides those objects to a class through different ways. Using DI, we move the creation and binding of the dependent objects outside of the class that depends on it.

.. note::
   Ubiquity only supports 2 types of injections, so as not to require introspection at execution:
   - property injection
   - setter injection
   Only controllers support dependency injection.

Service
-------
Create a service

.. code-block:: php
   :linenos:
   :caption: app/services/Service.php
   
   namespace services;

	class Service{
	    public function __construct($ctrl){
	        echo 'Service instanciation in '.get_class($ctrl);
	    }
	    
	    public function do($someThink=""){
	        echo 'do '.$someThink ."in service";
	    }
	}



.. |br| raw:: html

   <br />