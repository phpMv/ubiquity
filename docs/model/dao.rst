DAO
***

.. |br| raw:: html

   <br />

The **DAO** class is responsible for loading and persistence operations on models :

Connecting to the database
==========================
Check that the database connection parameters are correctly entered in the configuration file:

.. code-block:: bash
    
    Ubiquity config -f=database

Since 2.3.0 release

Database startup with ``DAO::startDatabase($config)`` in services.php file is useless, no need to start the database, the connection is made automatically at the first request.
Use ``DAO::start()`` in **app/config/services.php** file when using several databases (with multi db feature)


Loading data
============
Loading an instance
-------------------
Loading an instance of the `models\\User` class with id `5`

.. code-block:: php
    
    use Ubiquity\orm\DAO;
    use models\User;
        
    $user=DAO::getById(User::class, 5);

Loading an instance using a condition:

.. code-block:: php
    
    use Ubiquity\orm\DAO;
    use models\User;
        
    DAO::getOne(User::class, 'name= ?',false,['DOE']);

BelongsTo loading
^^^^^^^^^^^^^^^^^
By default, members defined by a **belongsTo** relationship are automatically loaded

Each user belongs to only one category:

.. code-block:: php
    
    $user=DAO::getById(User::class,5);
    echo $user->getCategory()->getName();
    
It is possible to prevent this default loading ; the third parameter allows the loading or not of belongsTo members:

.. code-block:: php
    
    $user=DAO::getOne(User::class,5, false);
    echo $user->getCategory();// NULL
    
HasMany loading
^^^^^^^^^^^^^^^
Loading **hasMany** members must always be explicit ; the third parameter allows the explicit loading of members.

Each user has many groups:

.. code-block:: php
    
    $user=DAO::getOne(User::class,5,['groupes']);
    foreach($user->getGroupes() as $groupe){
        echo $groupe->getName().'<br>';
    }

Composite primary key
^^^^^^^^^^^^^^^^^^^^^
Either the `ProductDetail` model corresponding to a product ordered on a command and whose primary key is composite:

.. tabs::

   .. tab:: Attributes

      .. code-block:: php
         :linenos:
         :caption: app/models/ProductDetail.php

          namespace models;

         use Ubiquity\attributes\items\Id;

          class ProductDetail{

            #[Id]
            private $idProduct;

            #[Id]
            private $idCommand;

            ...
          }

   .. tab:: Annotations

      .. code-block:: php
         :linenos:
         :caption: app/models/ProductDetail.php

          namespace models;

          class ProductDetail{
            /**
             * @id
             */
            private $idProduct;

            /**
             * @id
             */
            private $idCommand;

            ...
          }

The second parameter `$keyValues` can be an array if the primary key is composite:

.. code-block:: php
    
    $productDetail=DAO::getOne(ProductDetail::class,[18,'BF327']);
    echo 'Command:'.$productDetail->getCommande().'<br>';
    echo 'Product:'.$productDetail->getProduct().'<br>';
    
Loading multiple objects
------------------------
Loading instances of the `User` class:

.. code-block:: php
    
    $users=DAO::getAll(User::class);
    foreach($users as $user){
        echo $user->getName()."<br>";
    }

loading of related members
^^^^^^^^^^^^^^^^^^^^^^^^^^

Loading instances of the `User` class with its category and its groups :

.. code-block:: php
    
    $users=DAO::getAll(User::class,['groupes','category']);
    foreach($users as $user){
        echo "<h2>".$user->getName()."</h2>";
        echo $user->getCategory()."<br>";
        echo "<h3>Groups</h3>";
        echo "<ul>";
        foreach($user->getGroupes() as $groupe){
            echo "<li>".$groupe->getName()."</li>";
        }
        echo "</ul>";
    }

Descending in the hierarchy of related objects: |br|
Loading instances of the `User` class with its category, its groups and the organization of each group :

.. code-block:: php
    
    $users=DAO::getAll(User::class,['groupes.organization','category']);
    foreach($users as $user){
        echo "<h2>".$user->getName()."</h2>";
        echo $user->getCategory()."<br>";
        echo "<h3>Groups</h3>";
        echo "<ul>";
        foreach($user->getGroupes() as $groupe){
            echo "<li>".$groupe->getName()."<br>";
            echo "<li>".$groupe->getOrganization()->getName()."</li>";
        }
        echo "</ul>";
    }

Using wildcards: |br|

Loading instances of the `User` class with its category, its groups and all related members of each group:

.. code-block:: php
    
    $users=DAO::getAll(User::class,['groupes.*','category']);

Querying using conditions
-------------------------

Simple queries
^^^^^^^^^^^^^^

The `condition` parameter is equivalent to the WHERE part of an SQL statement:

.. code-block:: php
    
    $users=DAO::getAll(User::class,'firstName like "bren%" and not suspended',false);

To avoid SQL injections and benefit from the preparation of statements, it is preferable to perform a parameterized query:

.. code-block:: php
    
    $users=DAO::getAll(User::class,'firstName like ? and suspended= ?',false,['bren%',false]);
    
UQueries
^^^^^^^^

The use of **U-queries** allows to set conditions on associate members:

Selection of users whose organization has the domain **lecnam.net**:

.. code-block:: php
    
    $users=DAO::uGetAll(User::class,'organization.domain= ?',false,['lecnam.net']);

It is possible to view the generated request in the logs (if logging is enabled):

.. image:: /_static/images/dao/uquery-users-log.png
   :class: bordered
   
The result can be verified by selecting all users in this organization:

.. code-block:: php
    
    $organization=DAO::getOne(Organization::class,'domain= ?',['users'],['lecnam.net']);
    $users=$organization->getUsers();

The corresponding logs:

.. image:: /_static/images/dao/uquery-users-orga-log.png
   :class: bordered
   
Counting
--------

Existence testing
^^^^^^^^^^^^^^^^^

.. code-block:: php
        
    if(DAO::exists(User::class,'lastname like ?',['SMITH'])){
        //there's a Mr SMITH
    }

Counting
^^^^^^^^

To count the instances, what not to do, if users are not already loaded:

.. code-block:: php
        
    $users=DAO::getAll(User::class);
    echo "there are ". \count($users) ." users";

What needs to be done:

.. code-block:: php
        
    $count=DAO::count(User::class);
    echo "there are $count users";
   
With a condition:

.. code-block:: php
        
    $notSuspendedCount=DAO::count(User::class, 'suspended = ?', [false]);


with a condition on associated objects:

Number of users belonging to the **OTAN** named organization.

.. code-block:: php
        
    $count=DAO::uCount(User::class,'organization.name= ?',['OTAN']);


Modifying data
============
Adding an instance
------------------

Adding an organization:

.. code-block:: php
    
    $orga=new Organization();
    $orga->setName('Foo');
    $orga->setDomain('foo.net');
    if(DAO::save($orga)){
      echo $orga.' added in database';
    }

Adding an instance of User, in an organization:

.. code-block:: php
    
    $orga=DAO::getById(Organization::class, 1);
    $user=new User();
    $user->setFirstname('DOE');
    $user->setLastname('John');
    $user->setEmail('doe@bar.net');
    $user->setOrganization($orga);
    if(DAO::save($user)){
      echo $user.' added in database in '.$orga;
    }

Updating an instance
--------------------

First, the instance must be loaded:

.. code-block:: php
    
    $orga=DAO::getOne(Organization::class,'domain= ?',false,['foo.net']);
    $orga->setAliases('foo.org');
    if(DAO::save($orga)){
      echo $orga.' updated in database';
    }

Deleting an instance
--------------------

If the instance is loaded from database:

.. code-block:: php
    
    $orga=DAO::getById(Organization::class,5,false);
    if(DAO::remove($orga)){
      echo $orga.' deleted from database';
    }

If the instance is not loaded, it is more appropriate to use the `delete` method:

.. code-block:: php
    
    if(DAO::delete(Organization::class,5)){
      echo 'Organization deleted from database';
    }

Deleting multiple instances
===========================
Deletion of multiple instances without prior loading:

.. code-block:: php
    
   if($res=DAO::deleteAll(models\User::class, 'id in (?,?,?)',[1,2,3])){
       echo "$res elements deleted";
   }

Bulk queries
============
Bulk queries allow several operations (insertion, modification or deletion) to be performed in a single query, which contributes to improved performance.

Bulk inserts
------------

Insertions example:

.. code-block:: php
   
   $u = new User();
   $u->setName('Martin1');
   DAO::toInsert($u);
   $u = new User();
   $u->setName('Martin2');
   DAO::toInsert($u);
   //Perform inserts
   DAO::flushInserts();

Bulk updates
------------

Updates example:

.. code-block:: php
   
   $users = DAO::getAll(User::class, 'name like ?', false, [
      'Martin%'
   ]);
   foreach ($users as $user) {
      $user->setName(\strtoupper($user->getName()));
      DAO::toUpdate($user);
   }
   DAO::flushUpdates();

Bulk deletes
------------

Deletions example

.. code-block:: php
   
   $users = DAO::getAll(User::class, 'name like ?', false, [
   	'BULK%'
   ]);
   DAO::toDeletes($users);
   DAO::flushDeletes();


The `DAO::flush()` method can be called if insertions, updates or deletions are pending.

Transactions
============
Explicit transactions
---------------------
All DAO operations can be inserted into a transaction, so that a series of changes can be atomized:

.. code-block:: php
      
   try{
      DAO::beginTransaction();
      $orga=new Organization();
      $orga->setName('Foo');
      DAO::save($orga);
   
      $user=new User();
      $user->setFirstname('DOE');
      $user->setOrganization($orga);
      DAO::save($user);
      DAO::commit();
   }catch (\Exception $e){
      DAO::rollBack();
   }

In case of multiple databases defined in the configuration, transaction-related methods can take the database offset defined in parameter.

.. code-block:: php
   
   DAO::beginTransaction('db-messagerie');
   //some DAO operations on messagerie models
   DAO::commit('db-messagerie');

Implicit transactions
---------------------

Some DAO methods implicitly use transactions to group together insert, update or delete operations.

.. code-block:: php
   
   	    $users=DAO::getAll(User::class);
   	    foreach ($users as $user){
   	        $user->setSuspended(true);
   	        DAO::toUpdate($user);
   	    }
   	    DAO::updateGroups();//Perform updates in a transaction


SDAO class
==========
The **SDAO** class accelerates CRUD operations for the business classes without relationships.

Models must in this case declare public members only, and not respect the usual encapsulation.

.. code-block:: php
   :linenos:
   :caption: app/models/Product.php
   
    namespace models;
    class Product{
      /**
       * @id
       */
      public $id;

      public $name;
    
      ...
    }

The **SDAO** class inherits from **DAO** and has the same methods for performing CRUD operations.

.. code-block:: php
    
    use Ubiquity\orm\DAO;
        
    $product=DAO::getById(Product::class, 5);

Prepared DAO queries
====================
Preparing certain requests can improve performance with Swoole, Workerman or Roadrunner servers. |br|
This preparation initializes the objects that will then be used to execute the query. |br|
This initialization is done at server startup, or at the startup of each worker, if such an event exists.

Swoole sample
-------------

Preparation
^^^^^^^^^^^

.. code-block:: php
   :caption: app/config/swooleServices.php
   
   $swooleServer->on('workerStart', function ($srv) use (&$config) {
      \Ubiquity\orm\DAO::startDatabase($config);
      \Ubiquity\orm\DAO::prepareGetById('user', User::class);
      \Ubiquity\orm\DAO::prepareGetAll('productsByName', Product::class,'name like ?');
   });

Usage
^^^^^

.. code-block:: php
   :caption: app/controllers/UsersController.php
   
   public function displayUser($idUser){
      $user=DAO::executePrepared('user',[1]);
      echo $user->getName();
   }
   
   public function displayProducts($name){
      $products=DAO::executePrepared('productsByName',[$name]);
      ...
   }
