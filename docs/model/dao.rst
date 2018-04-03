DAO
***

.. |br| raw:: html

   <br />


The **DAO** class is responsible for loading and persistence operations on models :

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
Loading **hasMany** members must always be explicit ; the fourth parameter allows the loading of hasmany members.

Each user has many groups:

.. code-block:: php
    
    $user=DAO::getOne("models\User",5,true,true);
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
