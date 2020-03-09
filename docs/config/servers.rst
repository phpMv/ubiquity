.. _servers:
Servers configuration
=====================

Apache2
-------
mod_php/PHP-CGI
^^^^^^^^^^^^^^^
Apache 2.2
**********

.. code-block:: bash
   :caption: mydomain.conf
   
	<VirtualHost *:80>
	    ServerName mydomain.tld
	    ServerAlias www.mydomain.tld
	
	    DocumentRoot /var/www/project
	    <Directory /var/www/project>
	        # enable the .htaccess rewrites
	        AllowOverride All
	        Order Allow,Deny
	        Allow from All
	    </Directory>
	    
	    #No access to subfolders
	    <Directory /var/www/project/*/>
	        Order Allow,Deny
	        Deny from All
	    </Directory>
	
	    ErrorLog /var/log/apache2/project_error.log
	    CustomLog /var/log/apache2/project_access.log combined
	</VirtualHost>

.. info::
   
   Performance can be significantly improved by moving the rewrite rules from the **.htaccess** file to the VirtualHost block in your Apache configuration, and then changing ``AllowOverride All`` to ``AllowOverride None`` in your **VirtualHost** block.

Apache 2.4
**********
In Apache 2.4, ``Order Allow,Deny`` has been replaced by ``Require all granted``. 

.. code-block:: bash
   :caption: mydomain.conf
   
	<VirtualHost *:80>
	    ServerName mydomain.tld
	    ServerAlias www.mydomain.tld
	
	    DocumentRoot /var/www/project
	    <Directory /var/www/project>
	        # enable the .htaccess rewrites
	        AllowOverride All
	        Require all granted
	    </Directory>
	    
	    #No access to subfolders
	    <Directory /var/www/project/*/>
	        Require all denied
	    </Directory>
	
	    ErrorLog /var/log/apache2/project_error.log
	    CustomLog /var/log/apache2/project_access.log combined
	</VirtualHost>

index.php relocation in public folder
*************************************
Some may prefer to limit access to the **public** folder, and move **index.php** to that folder:

.. code-block:: php
   :caption: public/index.php
   
   <?php
   define('DS', DIRECTORY_SEPARATOR);
   //Updated with index.php in public folder
   define('ROOT', __DIR__ . DS . '../app' . DS);
   $config = include_once ROOT . 'config/config.php';
   require_once ROOT . './../vendor/autoload.php';
   require_once ROOT . 'config/services.php';
   \Ubiquity\controllers\Startup::run($config);

The **Virtualhost** block or the **.htaccess** file must in this case specify the new index directory:

.. code-block:: bash
   
   DirectoryIndex public/index.php

PHP-FPM
^^^^^^^

Make sure the **libapache2-mod-fastcgi** and **php7.x-fpm** packages are installed (replace **x** with php version number).

**php-pm** configuration:

.. code-block:: bash
   :caption: php-pm.conf
   
   ;;;;;;;;;;;;;;;;;;;;
   ; Pool Definitions ;
   ;;;;;;;;;;;;;;;;;;;;
   
   ; Start a new pool named 'www'.
   ; the variable $pool can be used in any directive and will be replaced by the
   ; pool name ('www' here)
   [www]
   
   user = www-data
   group = www-data
   
   ; use a unix domain socket
   listen = /var/run/php/php7.4-fpm.sock
   
   ; or listen on a TCP socket
   listen = 127.0.0.1:9000

**Apache 2.4** configuration:

.. code-block:: bash
   :caption: mydomain.conf
   
   <VirtualHost *:80>
   ...
      <FilesMatch \.php$>
           SetHandler proxy:fcgi://127.0.0.1:9000
           # for Unix sockets, Apache 2.4.10 or higher
           # SetHandler proxy:unix:/path/to/fpm.sock|fcgi://localhost/var/www/
       </FilesMatch>
    </VirtualHost>

nginX
-----

**nginX** configuration:

.. code-block:: bash
   :caption: nginx.conf
   
   upstream fastcgi_backend {
       server unix:/var/run/php/php7.4-fpm.sock;
       keepalive 50;
   }
   server {
       server_name mydomain.tld www.mydomain.tld;
       root /var/www/project;
       index index.php;
       listen 8080;

       location / {
           # try to serve file directly, fallback to index.php
           rewrite ^/(.*)$ /index.php?c=$1 last;
       }

       location = /index.php{
           fastcgi_pass fastcgi_backend;
           fastcgi_keep_conn on;
           fastcgi_param DOCUMENT_ROOT $realpath_root;
           fastcgi_param SCRIPT_FILENAME  $document_root/index.php;
           include /etc/nginx/fastcgi_params;
       }

       # return 404 for all other php files not matching the front controller
       # this prevents access to other php files you don't want to be accessible.
       location ~ \.php$ {
           return 404;
       }
   
       location /public/ {
           allow all;
           try_files $uri $uri/ =404;
       }
   
       location /.*/ {
          deny all;
       }
   
       error_log /var/log/nginx/project_error.log;
       access_log /var/log/nginx/project_access.log;
   }

Swoole
-----

**Swoole** configuration:


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

Workerman
---------

**Workerman** configuration:


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

RoadRunner
----------

**RoadRunner** configuration:

.. code-block:: yml
   :caption: .ubiquity/.rr.yml
   
   http:
     address:         ":8090"
     workers.command: "php-cgi ./.ubiquity/rr-worker.php"
     workers:
       pool:
         # Set numWorkers to 1 while debugging
         numWorkers: 10
         maxJobs:    1000
   
   # static file serving. remove this section to disable static file serving.
   static:
     # root directory for static file (http would not serve .php and .htaccess files).
     dir:   "."
   
     # list of extensions for forbid for serving.
     forbid: [".php", ".htaccess", ".yml"]
   
     always: [".ico", ".html", ".css", ".js"] 
