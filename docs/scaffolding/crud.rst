CRUD Controllers
================


The CRUD controllers allow you to perform basic operations on a Model class:
 - Create
 - Read
 - Update
 - Delete
 - ...
 
Creation
---------
 
 In the admin interface (web-tools), activate the **Controllers** part, and choose create **Crud controller**:

.. image:: /_static/images/crud/speControllerBtn.png

Then fill in the form:
  - Enter the controller name
  - Select the associated model
  - Then click on the validate button

.. image:: /_static/images/crud/createCrudForm1.png

Description of the features
---------------------------

The generated controller:

.. code-block:: php
   :linenos:
   :caption: app/controllers/Products.php
   
   <?php
   namespace controllers;
   
    /**
    * CRUD Controller UsersController
    **/
   class UsersController extends \Ubiquity\controllers\crud\CRUDController{
   
   	public function __construct(){
   		parent::__construct();
   		$this->model="models\\User";
   	}
   
   	public function _getBaseRoute() {
   		return 'UsersController';
   	}
   }
   
Test the created controller by clicking on the get button in front of the **index** action:

.. image:: /_static/images/crud/getBtn.png

Read (index action)
^^^^^^^^^^^^^^^^^^^

.. image:: /_static/images/crud/usersControllerIndex1.png

Clicking on a row of the dataTable (instance) displays the objects associated to the instance (**details** action):

.. image:: /_static/images/crud/usersControllerIndex1-details.png

Using the search area:

.. image:: /_static/images/crud/usersControllerSearch1.png


Create (newModel action)
^^^^^^^^^^^^^^^^^^^^^^^^
It is possible to create an instance by clicking on the add button

.. image:: /_static/images/crud/addNewModelBtn.png

The default form for adding an instance of User:

.. image:: /_static/images/crud/usersControllerNew1.png


Update (update action)
^^^^^^^^^^^^^^^^^^^^^^
The edit button on each row allows you to edit an instance

.. image:: /_static/images/crud/editModelBtn.png

The default form for adding an instance of User:

.. image:: /_static/images/crud/usersControllerEdit1.png


Delete (delete action)
^^^^^^^^^^^^^^^^^^^^^^
The delete button on each row allows you to edit an instance

.. image:: /_static/images/crud/deleteModelBtn.png

Display of the confirmation message before deletion:

.. image:: /_static/images/crud/usersControllerDelete1.png

Customization
-------------
Create again a CrudController from the admin interface:

.. image:: /_static/images/crud/createCrudForm2.png

It is now possible to customize the module using overriding.

Overview
^^^^^^^^

.. image:: /_static/images/crud/crud-schema.png

Classes overriding
^^^^^^^^^^^^^^^^^^

**ModelViewer**
+---------------------------------------------------------------+-------------------------------------------------------+
| Method                                                        | Signification                                         |
+===============================================================+=======================================================+
| index route                                                                                                           |
+---------------------------------------------------------------+-------------------------------------------------------+
| getModelDataTable($instances, $model,$totalCount,$page=1)     | Creates the dataTable and Adds its behavior           |
+---------------------------------------------------------------+-------------------------------------------------------+
| getDataTableInstance($instances,$model,$totalCount,$page=1)   | Creates the dataTable                                 |
+---------------------------------------------------------------+-------------------------------------------------------+
