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

If the database is to be used in all http requests, the connection can be located in the ``app/config/services.php`` file:

.. code-block:: php
    
    try{
    	\Ubiquity\orm\DAO::startDatabase($config);
    }catch(Exception $e){
    	echo $e->getMessage();
    }

If the database is only used on a part of the application, it is better to create a base controller for that part, and implement the connection in its override initialize method:

.. code-block:: php
   :linenos:
   :caption: app/controllers/ControllerWithDb.php
   
    namespace controllers;
    class ControllerWithDb extends ControllerBase{
    	public function initialize(){
    		$config=\Ubiquity\controllers\Startup::getConfig();
    		\Ubiquity\orm\DAO::startDatabase($config);
    	}
    }

Loading data
============
Loading an instance
-------------------
Loading an instance of the `models\\User` class with id `5`

.. code-block:: php
    
    use Ubiquity\orm\DAO;
        
    $user=DAO::getOne("models\User",5);

BelongsTo loading
^^^^^^^^^^^^^^^^^
By default, members defined by a **belongsTo** relationship are automatically loaded

Each user belongs to only one category:

.. code-block:: php
    
    $user=DAO::getOne("models\User",5);
    echo $user->getCategory()->getName();
    
It is possible to prevent this default loading ; the third parameter allows the loading or not of belongsTo members:

.. code-block:: php
    
    $user=DAO::getOne("models\User",5, false);
    echo $user->getCategory();// NULL
    
HasMany loading
^^^^^^^^^^^^^^^
Loading **hasMany** members must always be explicit ; the third parameter allows the explicit loading of members.

Each user has many groups:

.. code-block:: php
    
    $user=DAO::getOne("models\User",5,["groupes"]);
    foreach($user->getGroupes() as $groupe){
        echo $groupe->getName()."<br>";
    }

Composite primary key
^^^^^^^^^^^^^^^^^^^^^
Either the `ProductDetail` model corresponding to a product ordered on a command and whose primary key is composite:

.. code-block:: php
   :linenos:
   :caption: app/models/Products.php
   
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
    
    $productDetail=DAO::getOne("models\ProductDetail",[18,'BF327']);
    echo 'Command:'.$productDetail->getCommande().'<br>';
    echo 'Product:'.$productDetail->getProduct().'<br>';
    
Loading multiple objects
------------------------
Loading instances of the `User` class:

.. code-block:: php
    
    $users=DAO::getAll("models\User");
    foreach($users as $user){
        echo $user->getName()."<br>";
    }

loading of related members
^^^^^^^^^^^^^^^^^^^^^^^^^^

Loading instances of the `User` class with its category and its groups :

.. code-block:: php
    
    $users=DAO::getAll("models\User",["groupes","category"]);
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
    
    $users=DAO::getAll("models\User",["groupes.organization","category"]);
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
    
    $users=DAO::getAll("models\User",["groupes.*","category"]);

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
    
    $orga=DAO::getOne(Organization::class, 1);
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
    
    $orga=DAO::getOne(Organization::class,5,false);
    if(DAO::remove($orga)){
    	echo $orga.' deleted from database';
    }

If the instance is not loaded, it is more appropriate to use the `delete` method:

.. code-block:: php
    
    if(DAO::delete(Organization::class,5)){
    	echo 'Organization deleted from database';
    }
