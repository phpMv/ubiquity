.. _async:
Async platforms
===============
.. |br| raw:: html

   <br />

.. note:: Ubiquity supports multiple platforms : Swoole, Workerman, RoadRunner, PHP-PM, ngx_php.

Swoole
------

Install the Swoole extension on your system (linux) or in your Docker image :

.. code-block:: bash
   
   #!/bin/bash
   pecl install swoole
   
Run Ubiquity Swoole (for the first time, **ubiquity-swoole** package will be installed):

.. code-block:: bash
   
   Ubiquity serve -t=swoole
   
Server configuration
++++++++++++++++++++

.. code-block:: php
   :caption: .ubiquity/swoole-config.php
   
   <?php
   return array(
       "host" => "0.0.0.0",
       "port" => 8080,
       "options"=>[
           "worker_num" => \swoole_cpu_num() * 2,
	       "reactor_num" => \swoole_cpu_num() * 2
	   ]
   );
   
The port can also be changed at server startup:

.. code-block:: bash
   
   Ubiquity serve -t=swoole -p=8999
   
Services optimization
+++++++++++++++++++++
Startup of services will be done only once, at server startup.

.. code-block:: php
   :caption: app/config/services.php
   
   \Ubiquity\cache\CacheManager::startProd($config);
   \Ubiquity\orm\DAO::setModelsDatabases([
   	'models\\Foo' => 'default',
   	'models\\Bar' => 'default'
   ]);
   
   \Ubiquity\cache\CacheManager::warmUpControllers([
   	\controllers\IndexController::class,
   	\controllers\FooController::class
   ]);
   
   $swooleServer->on('workerStart', function ($srv) use (&$config) {
   	\Ubiquity\orm\DAO::startDatabase($config, 'default');
   	\controllers\IndexController::warmup();
   	\controllers\FooController::warmup();
   });

The warmUpControllers method:
  - instantiates the controllers
  - performs dependency injection
  - prepares the call of the initialize and finalize methods (initialization of call constants)
   
At the start of each Worker, the **warmup** method of the controllers can for example initialize prepared DAO queries:

.. code-block:: php
   :caption: app/controllers/FooController.php
   
   	public static function warmup() {
   		self::$oneFooDao = new DAOPreparedQueryById('models\\Foo');
   		self::$allFooDao = new DAOPreparedQueryAll('models\\Foo');
   	}

Workerman
---------

Workerman does not require any special installation (except for **libevent** to be used in production for performance reasons).

   
Run Ubiquity Workerman (for the first time, **ubiquity-workerman** package will be installed):

.. code-block:: bash
   
   Ubiquity serve -t=workerman
   
Server configuration
++++++++++++++++++++

.. code-block:: php
   :caption: .ubiquity/workerman-config.php
   
   <?php
   return array(
       "host" => "0.0.0.0",
       "port" => 8080,
       "socket"=>[
           "count" => 4,
           "reuseport" =>true
       ]
   );
   
The port can also be changed at server startup:

.. code-block:: bash
   
   Ubiquity serve -t=workerman -p=8999
   
Services optimization
+++++++++++++++++++++

Startup of services will be done only once, at server startup.

.. code-block:: php
   :caption: app/config/services.php
   
   \Ubiquity\cache\CacheManager::startProd($config);
   \Ubiquity\orm\DAO::setModelsDatabases([
   	'models\\Foo' => 'default',
   	'models\\Bar' => 'default'
   ]);
   
   \Ubiquity\cache\CacheManager::warmUpControllers([
   	\controllers\IndexController::class,
   	\controllers\FooController::class
   ]);
   
   $workerServer->onWorkerStart = function () use ($config) {
   	\Ubiquity\orm\DAO::startDatabase($config, 'default');
   	\controllers\IndexController::warmup();
   	\controllers\FooController::warmup();
   });
   
ngx_php
---------

//TODO

Roadrunner
----------

//TODO
