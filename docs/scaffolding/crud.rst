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

ModelViewer methods to override
###############################

+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+
| Method                                                            | Signification                                                                   | Default return    |
+===================================================================+=================================================================================+===================+
| **index** route                                                                                                                                                         |
+-------------------------------------------------------------------+-----------------------------------------------------------------------------------------------------+
| getModelDataTable($instances, $model,$totalCount,$page=1)         | Creates the dataTable and Adds its behavior                                     | DataTable         |
+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+
| getDataTableInstance($instances,$model,$totalCount,$page=1)       | Creates the dataTable                                                           | DataTable         |
+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+
| recordsPerPage($model,$totalCount=0)                              | Returns the count of rows to display (if null there's no pagination)            | null or 6         |
+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+
| getGroupByFields()                                                | Returns an array of members on which to perform a grouping                      | []                |
+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+
| getDataTableRowButtons()                                          | Returns an array of buttons to display for each row ["edit","delete","display"] | ["edit","delete"] |
+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+
| onDataTableRowButton(HtmlButton $bt)                              | To override for modifying the dataTable row buttons                             |                   |
+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+
| getCaptions($captions, $className)                                | Returns the captions of the column headers                                      | all members       |
+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+
| **detail** route                                                                                                                                                        |
+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+
| showDetailsOnDataTableClick()                                     | To override to make sure that the detail of a clicked object is displayed or not| true              |
+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+
| onDisplayFkElementListDetails($element,$member,$className,$object)| To modify for displaying each element in a list component of foreign objects    |                   |
+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+
| getFkHeaderElementDetails($member, $className, $object)           | Returns the header for a single foreign object (issue from ManyToOne)           | HtmlHeader        |
+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+
| getFkElementDetails($member, $className, $object)                 | Returns a component for displaying a single foreign object (manyToOne relation) | HtmlLabel         |
+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+
| getFkHeaderListDetails($member, $className, $list)                | Returns the header for a list of foreign objects (oneToMany or ManyToMany)      | HtmlHeader        |
+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+
| getFkListDetails($member, $className, $list)                      | Returns a list component for displaying a collection of foreign objects (many)  | HtmlList          |
+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+
