.. _di:
Dependency injection
====================

.. note::
   For performance reasons, dependency injection is not used in the core part of the framework.

Dependency Injection (DI) is a design pattern used to implement IoC. |br|
It allows the creation of dependent objects outside of a class and provides those objects to a class through different ways. Using DI, we move the creation and binding of the dependent objects outside of the class that depends on it.

.. note::
   Ubiquity only supports property injection, so as not to require introspection at execution. |br|
   Only controllers support dependency injection.

Service autowiring
------------------
Service creation
++++++++++++++++

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

Autowiring in Controller
++++++++++++++++++++++++

Create a controller that requires the service

.. code-block:: php
   :linenos:
   :caption: app/services/Service.php
   
	namespace controllers;
	
	 /**
	 * Controller Client
	 **/
	class ClientController extends ControllerBase{
	
		/**
		 * @autowired
		 * @var services\Service
		 */
		private $service;
		
		public function index(){}
	
		/**
		 * @param \services\Service $service
		 */
		public function setService($service) {
			$this->service = $service;
		}
	}

In the above example, Ubiquity looks for and injects **$service** when **ClientController** is created.

The **@autowired** annotation requires that:
  - the type to be instantiated is declared with the **@var** annotation
  - **$service** property has a setter, or whether declared public

As the annotations are never read at runtime, it is necessary to generate the cache of the controllers:

.. code-block:: bash
   
   Ubiquity init-cache -t=controllers

It remains to check that the service is injected by going to the address ``/ClientController``.

Service injection
-----------------
Service
+++++++

Let's now create a second service, requiring a special initialization.

.. code-block:: php
   :linenos:
   :caption: app/services/ServiceWithInit.php
   
	class ServiceWithInit{
		private $init;
		
		public function init(){
			$this->init=true;
		}
		
		public function do(){
			if($this->init){
				echo 'init well initialized!';
			}else{
				echo 'Service not initialized';
			}
		}
	}

Injection in controller
+++++++++++++++++++++++

.. code-block:: php
   :linenos:
   :caption: app/controllers/ClientController.php
   :emphasize-lines: 15
   
   namespace controllers;

	 /**
	 * Controller Client
	 **/
	class ClientController extends ControllerBase{
	
		/**
		 * @autowired
		 * @var \services\Service
		 */
		private $service;
		
		/**
		 * @injected
		 */
		private $serviceToInit;
		
		public function index(){
			$this->serviceToInit->do();
		}
	
		/**
		 * @param \services\Service $service
		 */
		public function setService($service) {
			$this->service = $service;
		}
		
		/**
		 * @param mixed $serviceToInit
		 */
		public function setServiceToInit($serviceToInit) {
			$this->serviceToInit = $serviceToInit;
		}
	
	}

Di declaration
++++++++++++++

In ``app/config/config.php``, create a new key for **serviceToInit** property to inject in **di** part.

.. code-block:: php
   
		"di"=>["ClientController.serviceToInit"=>function(){
					$service=new \services\ServiceWithInit();
					$service->init();
					return $service;
				}
			]

generate the cache of the controllers:

.. code-block:: bash
   
   Ubiquity init-cache -t=controllers

Check that the service is injected by going to the address ``/ClientController``.

.. note::
   If the same service is to be used in several controllers, use the wildcard notation :
   
   .. code-block:: php
      
   		"di"=>["*.serviceToInit"=>function(){
   					$service=new \services\ServiceWithInit();
   					$service->init();
   					return $service;
   				}
   			]

Injection with a qualifier name
+++++++++++++++++++++++++++++++

If the name of the service to be injected is different from the key of the **di** array, it is possible to use the name attribute of the **@injected** annotation

In ``app/config/config.php``, create a new key for **serviceToInit** property to inject in **di** part.

.. code-block:: php
   
		"di"=>["*.service"=>function(){
					$service=new \services\ServiceWithInit();
					$service->init();
					return $service;
				}
			]

.. code-block:: php
   
		/**
		 * @injected("service")
		 */
		private $serviceToInit;
		


Service injection at runtime
----------------------------

It is possible to inject services at runtime, without these having been previously declared in the controller classes.

.. code-block:: php
   :linenos:
   :caption: app/services/RuntimeService.php
   
   namespace services;

	class RuntimeService{
	    public function __construct($ctrl){
	        echo 'Service instanciation in '.get_class($ctrl);
	    }
	}

In ``app/config/config.php``, create the **@exec** key in **di** part.

.. code-block:: php
   
		"di"=>["@exec"=>"rService"=>function($ctrl){
					return new \services\RuntimeService($ctrl);
				}
			]

With this declaration, the **$rService** member, instance of **RuntimeService**, is injected into all the controllers. |br|
It is then advisable to use the javadoc comments to declare **$rService** in the controllers that use it (to get the code completion on **$rService** in your IDE).

.. code-block:: php
   :linenos:
   :caption: app/controllers/MyController.php
   :emphasize-lines: 5,10
   
   namespace controllers;

	 /**
	 * Controller Client
	 * property services\RuntimeService $rService
	 **/
	class MyController extends ControllerBase{
	
		public function index(){
			$this->rService->do();
		}
	}


.. |br| raw:: html

   <br />