Project configuration
=====================
Normally, the installer limits the modifications to be performed in the configuration files and your application is operational after installation

.. image:: _static/firstProject.png

Main configuration
------------------
The main configuration of a project is localised in the ``app/conf/config.php`` file.

.. code-block:: php
   :linenos:
   :caption: app/conf/config.php
   
   return array(
   		"siteUrl"=>"%siteUrl%",
   		"database"=>[
   				"dbName"=>"%dbName%",
   				"serverName"=>"%serverName%",
   				"port"=>"%port%",
   				"user"=>"%user%",
   				"password"=>"%password%"
   		],
   		"namespaces"=>[],
   		"templateEngine"=>'Ubiquity\views\engine\Twig',
   		"templateEngineOptions"=>array("cache"=>false),
   		"test"=>false,
   		"debug"=>false,
   		"di"=>[%injections%],
   		"cacheDirectory"=>"cache/",
   		"mvcNS"=>["models"=>"models","controllers"=>"controllers"]
   );
Services configuration
----------------------
Services loaded on startup are configured in the ``app/conf/services.php`` file.

.. code-block:: php
   :linenos:
   :caption: app/conf/services.php
   
   use Ubiquity\cache\CacheManager;
   use Ubiquity\controllers\Router;
   use Ubiquity\orm\DAO;
   
   /*if($config["test"]){
   \Ubiquity\log\Logger::init();
   $config["siteUrl"]="http://127.0.0.1:8090/";
   }*/
   
   $db=$config["database"];
   if($db["dbName"]!==""){
   	DAO::connect($db["dbName"],@$db["serverName"],@$db["port"],@$db["user"],@$db["password"]);
   }
   CacheManager::startProd($config);
   Router::start();
   Router::addRoute("_default", "controllers\Main");
Pretty URLs
-----------
Apache
^^^^^^
The framework ships with an **.htaccess** file that is used to allow URLs without index.php. If you use Apache to serve your Ubiquity application, be sure to enable the **mod_rewrite** module.

.. code-block:: bash
   :caption: .htaccess
   
   AddDefaultCharset UTF-8
   <IfModule mod_rewrite.c>
   	RewriteEngine On
   	RewriteBase /blog/
   	RewriteCond %{REQUEST_FILENAME} !-f  
   	RewriteCond %{HTTP_ACCEPT} !(.*images.*)
   	RewriteRule ^(.*)$ index.php?c=$1 [L,QSA]
   </IfModule>

Nginx
^^^^^
On Nginx, the following directive in your site configuration will allow "pretty" URLs:

.. code-block:: php
   
   location / {
       try_files $uri $uri/ /index.php?c=$query_string;
   }
