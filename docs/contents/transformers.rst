.. _transformers:
Transformers
============

.. note::
   The Transformers module uses the static class **TransformersManager** to manage data transformations.
   

Transformers are used to transform datas after loading from the database, or before displaying in a view.

Adding transformers
-------------------

Either the **Author** class that we want to use in our application :

.. tabs::

   .. tab:: Attributes

      .. code-block:: php
         :linenos:
         :caption: app/models/Author.php
         :emphasize-lines: 7

         namespace models;

         use Ubiquity\attributes\items\Transformer;

         class Author {

            #[Transformer('upper')]
            private $name;

            public function getName(){
               return $this->name;
            }

            public function setName($name){
               $this->name=$name;
            }
         }

   .. tab:: Annotations

      .. code-block:: php
         :linenos:
         :caption: app/models/Author.php
         :emphasize-lines: 6

         namespace models;

         class Author {
            /**
             * @var string
             * @transformer("upper")
             */
            private $name;

            public function getName(){
               return $this->name;
            }

            public function setName($name){
               $this->name=$name;
            }
         }

We added a transformer on the **name** member with the **@transformer** annotation, in order to capitalize the name in the views.

Generating cache
----------------
Run this command in console mode to create the cache data of the **Author** class :

.. code-block:: bash
   
   Ubiquity init-cache -t=models

transformer cache is generated with model metadatas in ``app/cache/models/Author.cache.php``.

Transformers informations can be displayed with devtools :

.. code-block:: bash
   
   Ubiquity info:model -m=Author -f=#transformers
   

.. image:: /_static/images/transformers/trans-info.png
   :class: console

Using transformers
------------------

Start the **TransformersManager** in the file `app/config/services.php`:

.. code-block:: php
   :caption: app/config/services.php
   
   \Ubiquity\contents\transformation\TransformersManager::startProd();

You can test the result in the administration interface:

.. image:: /_static/images/transformers/trans-upper.png
   :class: bordered

or by creating a controller:

.. code-block:: php
   :linenos:
   :caption: app/controllers/Authors.php
   
   namespace controllers;

   class Authors {

      public function index(){
         DAO::transformersOp='toView';
         $authors=DAO::getAll(Author::class);
         $this->loadDefaultView(['authors'=>$authors]);
      }

   }


.. code-block:: html
   :caption: app/views/Authors/index.html
   
   <ul>
      {% for author in authors %}
         <li>{{ author.name }}</li>
      {% endfor %}
   </ul>

Transformer types
-----------------

transform
+++++++++
The **transform** type is based on the **TransformerInterface** interface. It is used when the transformed data must be converted into an object. |br|
The **DateTime** transformer is a good example of such a transformer:

- When loading the data, the Transformer converts the date from the database into an instance of php DateTime.
- Its **reverse** method performs the reverse operation (php date to database compatible date). 

toView
++++++
The **toView** type is based on the **TransformerViewInterface** interface. It is used when the transformed data must be displayed in a view. |br|

toForm
++++++
The **toForm** type is based on the **TransformerFormInterface** interface. It is used when the transformed data must be used in a form. |br|

Transformers usage
------------------
Transform on data loading
+++++++++++++++++++++++++
If ommited, default **transformerOp** is **transform**

.. code-block:: php
   
   $authors=DAO::getAll(Author::class);


Set transformerOp to **toView**

.. code-block:: php
   
   DAO::transformersOp='toView';
   $authors=DAO::getAll(Author::class);
   
Transform after loading
+++++++++++++++++++++++
Return the transformed member value:

.. code-block:: php
   
   TransformersManager::transform($author, 'name','toView');

Return a transformed value:

.. code-block:: php
   
   TransformersManager::applyTransformer($author, 'name','john doe','toView');


Transform an instance by applying all defined transformers:

.. code-block:: php
   
   TransformersManager::transformInstance($author,'toView');

Existing transformers
---------------------
+------------+---------------------------+----------------------------------------------------------------+
|Transformer |Type(s)                    |Description                                                     |
+------------+---------------------------+----------------------------------------------------------------+
|datetime    |transform, toView, toForm  |Transform a database datetime to a php DateTime object          | 
+------------+---------------------------+----------------------------------------------------------------+
|upper       |toView                     |Make the member value uppercase                                 |
+------------+---------------------------+----------------------------------------------------------------+
|lower       |toView                     |Make the member value lowercase                                 |
+------------+---------------------------+----------------------------------------------------------------+
|firstUpper  |toView                     |Make the member value first character uppercase                 |
+------------+---------------------------+----------------------------------------------------------------+
|password    |toView                     |Mask the member characters                                      |
+------------+---------------------------+----------------------------------------------------------------+
|md5         |toView                     |Hash the value with md5                                         |
+------------+---------------------------+----------------------------------------------------------------+

Create your own
---------------
Creation
++++++++

Create a transformer to display a user name as a local email address:

.. code-block:: php
   :linenos:
   :caption: app/transformers/toLocalEmail.php
   
   namespace transformers;
   use Ubiquity\contents\transformation\TransformerViewInterface;

   class ToLocalEmail implements TransformerViewInterface{

      public static function toView($value) {
         if($value!=null) {
            return sprintf('%s@mydomain.local',strtolower($value));
         }
      }

   }

Registration
++++++++++++

Register the transformer by executing the following script:

.. code-block:: php
   
   TransformersManager::registerClassAndSave('localEmail',\transformers\ToLocalEmail::class);


Usage
+++++

.. tabs::

   .. tab:: Attributes

      .. code-block:: php
         :linenos:
         :caption: app/models/User.php
         :emphasize-lines: 7

         namespace models;

         use Ubiquity\attributes\items\Transformer;

         class User {

            #[Transformer('localEmail')]
            private $name;

            public function getName(){
               return $this->name;
            }

            public function setName($name){
               $this->name=$name;
            }
         }

   .. tab:: Annotations

      .. code-block:: php
         :linenos:
         :caption: app/models/User.php
         :emphasize-lines: 6

         namespace models;

         class User {
            /**
             * @var string
             * @transformer("localEmail")
             */
            private $name;

            public function getName(){
               return $this->name;
            }

            public function setName($name){
               $this->name=$name;
            }
         }

.. code-block:: php
   
   DAO::transformersOp='toView';
   $user=DAO::getOne(User::class,"name='Smith'");
   echo $user->getName();

**Smith** user name will be displayed as **smith@mydomain.local**.

.. |br| raw:: html

   <br />
