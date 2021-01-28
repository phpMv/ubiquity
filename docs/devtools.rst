Devtools usage
==============

Project creation
----------------
See :doc:`createproject` to create a project.

.. tip:: For all other commands, you must be in your project folder or one of its subfolders.

.. important:: 
   The ``.ubiquity`` folder created automatically with the project allows the devtools to find the root folder of the project. |br|
   If it has been deleted or is no longer present, you must recreate this empty folder.

Controller creation
-------------------
Specifications
++++++++++++++

- command : ``controller``
- Argument : ``controller-name``
- aliases : ``create-controller``

Parameters
++++++++++

+------------+------------+------------------------------------+-----------+-----------------------+
| short name | name       | role                               | default   | Allowed values        |
+============+============+====================================+===========+=======================+
|      v     | view       | Creates the associated view index. |   true    | true, false           |
+------------+------------+------------------------------------+-----------+-----------------------+

Samples:
+++++++
Creates the controller ``controllers\ClientController`` class in ``app/controllers/ClientController.php``:

.. code-block:: bash
   
   Ubiquity controller ClientController

Creates the controller ``controllers\ClientController`` class in ``app/controllers/ClientController.php`` and the associated view in ``app/views/ClientController/index.html``:

.. code-block:: bash
   
   Ubiquity controller ClientController -v

Action creation
---------------
Specifications
++++++++++++++

- command : ``action``
- Argument : ``controller-name.action-name``
- aliases : ``new-action``

Parameters
++++++++++

+------------+---------------+---------------------------------------+-----------+-----------------------+
| short name | name          | role                                  | default   | Allowed values        |
+============+===============+=======================================+===========+=======================+
|      p     | params        | The action parameters (or arguments). |           | a,b=5 or $a,$b,$c     |
+------------+---------------+---------------------------------------+-----------+-----------------------+
|      r     | route         | The associated route path.            |           | /path/to/route        |
+------------+---------------+---------------------------------------+-----------+-----------------------+
|      v     | create-view   | Creates the associated view.          | false     | true,false            |
+------------+---------------+---------------------------------------+-----------+-----------------------+

Samples:
+++++++
Adds the action ``all`` in controller ``Users``:

.. code-block:: bash
   
   Ubiquity action Users.all
   
code result:

.. code-block:: php
   :linenos:
   :caption: app/controllers/Users.php
   :emphasize-lines: 9-11

   namespace controllers;
   /**
    * Controller Users
    */
   class Users extends ControllerBase{

      public function index(){}

      public function all(){

      }

   }


Adds the action ``display`` in controller ``Users`` with a parameter:

.. code-block:: bash
   
   Ubiquity action Users.display -p=idUser
 
.. info::
   The parameters must respect the php naming rules for the variables. | Br |
   You do not have to put the **$** in front of the parameter names.

code result:

.. code-block:: php
   :linenos:
   :caption: app/controllers/Users.php
   :emphasize-lines: 5-7
   
   class Users extends ControllerBase{

      public function index(){}

      public function display($idUser){

      }
   }

Adds the action ``display`` with an associated route:

.. code-block:: bash
   
   Ubiquity action Users.display -p=idUser -r=/users/display/{idUser}

code result:

.. tabs::

   .. tab:: Attributes

      .. code-block:: php
         :linenos:
         :caption: app/controllers/Users.php
         :emphasize-lines: 9-12

         namespace controllers;

         use Ubiquity\attributes\items\router\Route;

         class Users extends ControllerBase{

            public function index(){}

            #[Route('/users/display/{idUser}')]
            public function display($idUser){

            }
         }

   .. tab:: Annotations

      .. code-block:: php
         :linenos:
         :caption: app/controllers/Users.php
         :emphasize-lines: 7-12

         namespace controllers;

         class Users extends ControllerBase{

            public function index(){}

            /**
             *@route("/users/display/{idUser}")
             */
            public function display($idUser){

            }
         }

Adds the action ``search`` with multiple parameters:

.. code-block:: bash
   
   Ubiquity action Users.search -p=name,address=''

code result:

.. tabs::

   .. tab:: Attributes

      .. code-block:: php
         :linenos:
         :caption: app/controllers/Users.php
         :emphasize-lines: 14-16

         namespace controllers;

         use Ubiquity\attributes\items\router\Route;

         class Users extends ControllerBase{

            public function index(){}

            #[Route('/users/display/{idUser}')]
            public function display($idUser){

            }

            public function search($name,$address=''){

            }
         }

   .. tab:: Annotations

      .. code-block:: php
         :linenos:
         :caption: app/controllers/Users.php
         :emphasize-lines: 14-16

         namespace controllers;

         class Users extends ControllerBase{

            public function index(){}

            /**
             * @route("/users/display/{idUser}")
             */
            public function display($idUser){

            }

            public function search($name,$address=''){

            }
         }

Adds the action ``search`` and creates the associated view:

.. code-block:: bash
   
   Ubiquity action Users.search -p=name,address -v

                  
Model creation
--------------

.. note:: Optionally check the database connection settings in the app/config/config.php file before running these commands.

To generate a model corresponding to the **user** table in database:

.. code-block:: bash
   
   Ubiquity model user

All models creation
-------------------

For generating all models from the database:

.. code-block:: bash
   
   Ubiquity all-models

Cache initialization
--------------------
To initialize the cache for routing (based on annotations in controllers) and orm (based on annotations in models) :

.. code-block:: bash
   
   Ubiquity init-cache

.. |br| raw:: html

   <br />
