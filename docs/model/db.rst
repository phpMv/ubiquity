.. _db:
Database
********

.. |br| raw:: html

   <br />

The **DAO** class is responsible for loading and persistence operations on models :

Connecting to the database
==========================

Check that the database connection parameters are correctly entered in the configuration file:

.. code-block:: bash
    
    Ubiquity config -f=database

.. image:: /_static/images/dao/db-config.png
   :class: console

Transparent connection
---------------------
Since Ubiquity 2.3.0, The connection to the database is done automatically the first time you request it:

.. code-block:: php
    
    use Ubiquity\orm\DAO;
    
    $firstUser=DAO::getById(User::class,1);//Automatically start the database

This is the case for all methods in the **DAO** class used to perform CRUD operations.

Explicit connection
-------------------

In some cases, however, it may be useful to make an explicit connection to the database, especially to check the connection.

.. code-block:: php
    
    use Ubiquity\orm\DAO;
    use Ubiquity\controllers\Startup;
    ...
    try{
    	$config=\Ubiquity\controllers\Startup::getConfig();
    	DAO::startDatabase($config);
    	$users=DAO::getAll(User::class,'');
    }catch(Exception $e){
    	echo $e->getMessage();
    }


Multiple connections
====================
Adding a new connection
-----------------------

Ubiquity allows you to manage several connections to databases.

With Webtools
^^^^^^^^^^^^^^

In the **Models** part, choose **Add new connection** button:

.. image:: /_static/images/dao/add-new-co-btn.png
   :class: bordered

Define the connection configuration parameters:

.. image:: /_static/images/dao/new-co.png
   :class: bordered

Generate models for the new connection:|br|
The generated models include the ``@database`` annotation mentioning their link to the connection.

.. code-block:: php
    
    <?php
    namespace models\tests;
    /**
     * @database('tests')
     * @table('groupe')
    */
    class Groupe{
    	...
    }

Models are generated in a sub-folder of ``models``.

With several connections, do not forget to add the following line to the ``services.php`` file:

.. code-block:: php
    
    \Ubiquity\orm\DAO::start();
    

The ``start`` method performs the match between each model and its associated connection.