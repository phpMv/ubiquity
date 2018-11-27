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

CRUDController methods to override
##################################

+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+
| Method                                                            | Signification                                                                   | Default return    |
+===================================================================+=================================================================================+===================+
| routes                                                                                                                                                                  |
+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+
| index()                                                           | Default page : list all objects                                                 |                   |
+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+
| edit($modal="no", $ids="")                                        | Edits an instance                                                               |                   |
+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+
| newModel($modal="no")                                             | Creates a new instance                                                          |                   |
+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+
| display($modal="no",$ids="")                                      | Displays an instance                                                            |                   |
+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+
| delete($ids)                                                      | Deletes an instance                                                             |                   |
+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+
| update()                                                          | Displays the result of an instance updating                                     |                   |
+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+
| showDetail($ids)                                                  | Displays associated members with foreign keys                                   |                   |
+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+
| refresh_()                                                        | Refreshes the area corresponding to the DataTable (#lv)                         |                   |
+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+
| refreshTable($id=null)                                            | //TO COMMENT                                                                    |                   |
+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+


ModelViewer methods to override
###############################

+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+
| Method                                                            | Signification                                                                   | Default return    |
+===================================================================+=================================================================================+===================+
| **index** route                                                                                                                                                         |
+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+
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
| getCaptions($captions, $className)                                | Returns the captions of the column headers                                      | all member names  |
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
| **edit** and **newModel** routes                                                                                                                                        |
+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+
| getForm($identifier, $instance)                                   | Returns the form for adding or modifying an object                              | HtmlForm          |
+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+
| getFormTitle($form,$instance)                                     | Returns an associative array defining form message title                        |                   |
|                                                                   | with keys "icon","message","subMessage"                                         | HtmlForm          |
+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+
| setFormFieldsComponent(DataForm $form,$fieldTypes)                | Sets the components for each field                                              |                   |
+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+
| onGenerateFormField($field)                                       | For doing something when $field is generated in form                            |                   |
+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+
| isModal($objects, $model)                                         | Condition to determine if the edit or add form is modal for $model objects      | count($objects)>5 |
+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+
| getFormCaptions($captions, $className, $instance)                 | Returns the captions for form fields                                            | all member names  |
+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+
| **display** route                                                                                                                                                       |
+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+
| getModelDataElement($instance,$model,$modal)                      | Returns a DataElement object for displaying the instance                        | DataElement       |
+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+
| getElementCaptions($captions, $className, $instance)              | Returns the captions for DataElement fields                                     | all member names  |
+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+
| **delete** route                                                                                                                                                        |
+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+
| onConfirmButtons(HtmlButton $confirmBtn,HtmlButton $cancelBtn)    | To override for modifying delete confirmation buttons                           |                   |
+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+

CRUDDatas methods to override
###############################

+--------------------------------------------------------------+---------------------------------------------------------------------------------+------------------------+
| Method                                                       | Signification                                                                   | Default return         |
+==============================================================+=================================================================================+========================+
| **index** route                                                                                                                                                         |
+--------------------------------------------------------------+---------------------------------------------------------------------------------+------------------------+
| _getInstancesFilter($model)                                  | Adds a condition for filtering the instances displayed in dataTable             | 1=1                    |
+--------------------------------------------------------------+---------------------------------------------------------------------------------+------------------------+
| getFieldNames($model)                                        | Returns the fields to display in the **index** action for $model                | all member names       |
+--------------------------------------------------------------+---------------------------------------------------------------------------------+------------------------+
| getSearchFieldNames($model)                                  | Returns the fields to use in search queries                                     | all member names       |
+--------------------------------------------------------------+---------------------------------------------------------------------------------+------------------------+
| **edit** and **newModel** routes                                                                                                                                        |
+--------------------------------------------------------------+---------------------------------------------------------------------------------+------------------------+
| getFormFieldNames($model,$instance)                          | Returns the fields to update in the **edit** and **newModel** actions for $model| all member names       |
+--------------------------------------------------------------+---------------------------------------------------------------------------------+------------------------+
| getManyToOneDatas($fkClass,$instance,$member)                | Returns a list (filtered) of $fkClass objects to display in an html list        | all $fkClass instances |
+--------------------------------------------------------------+---------------------------------------------------------------------------------+------------------------+
| getOneToManyDatas($fkClass,$instance,$member)                | Returns a list (filtered) of $fkClass objects to display in an html list        | all $fkClass instances |
+--------------------------------------------------------------+---------------------------------------------------------------------------------+------------------------+
| getManyToManyDatas($fkClass,$instance,$member)               | Returns a list (filtered) of $fkClass objects to display in an html list        | all $fkClass instances |
+--------------------------------------------------------------+---------------------------------------------------------------------------------+------------------------+
| **display** route                                                                                                                                                       |
+--------------------------------------------------------------+---------------------------------------------------------------------------------+------------------------+
| getElementFieldNames($model)                                 | Returns the fields to display in the **display** action for $model              | all member names       |
+--------------------------------------------------------------+---------------------------------------------------------------------------------+------------------------+


CRUDEvents methods to override
###############################

+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+
| Method                                                            | Signification                                                                   | Default return    |
+===================================================================+=================================================================================+===================+
| **index** route                                                                                                                                                         |
+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+
| onConfDeleteMessage(CRUDMessage $message,$instance)               | Returns the confirmation message displayed before deleting an instance          | CRUDMessage       |
+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+
| onSuccessDeleteMessage(CRUDMessage $message,$instance)            | RReturns the message displayed after a deletion                                 | CRUDMessage       |
+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+
| onErrorDeleteMessage(CRUDMessage $message,$instance)              | Returns the message displayed when an error occurred when deleting              | CRUDMessage       |
+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+
| **edit** and **newModel** routes                                                                                                                                        |
+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+
| onSuccessUpdateMessage(CRUDMessage $message)                      | Returns the message displayed when an instance is added or inserted             | CRUDMessage       |
+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+
| onErrorUpdateMessage(CRUDMessage $message)                        | Returns the message displayed when an error occurred when updating or inserting | CRUDMessage       |
+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+
| all routes                                                                                                                                                              |
+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+
| onNotFoundMessage(CRUDMessage $message,$ids)                      | Returns the message displayed when an instance does not exists                  |                   |
+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+
| onDisplayElements($dataTable,$objects,$refresh)                   | Triggered after displaying objects in dataTable                                 |                   |
+-------------------------------------------------------------------+---------------------------------------------------------------------------------+-------------------+


CRUDFiles methods to override
###############################

+-------------------------------------------------------------------+-----------------------------------------------------------------+-----------------------------------+
| Method                                                            | Signification                                                   | Default return                    |
+===================================================================+=================================================================+===================================+
| template files                                                                                                                                                          |
+---------------------------------+---------------------------------------------------------------------------------------------------+-----------------------------------+
| getViewBaseTemplate()           | Returns the base template for all Crud actions if getBaseTemplate return a base template filename | @framework/crud/baseTemplate.html |
+---------------------------------+---------------------------------------------------------------------------------------------------+-----------------------------------+
| getViewIndex()                  | Returns the template for the **index** route                                                      | @framework/crud/index.html        |
+---------------------------------+---------------------------------------------------------------------------------------------------+-----------------------------------+
| getViewForm()                   | Returns the template for the **edit** and **newInstance** routes                                  | @framework/crud/form.html         |
+---------------------------------+---------------------------------------------------------------------------------------------------+-----------------------------------+
| getViewDisplay()                | Returns the template for the **display** route                                                    | @framework/crud/display.html      |
+---------------------------------+---------------------------------------------------------------------------------------------------+-----------------------------------+
| Urls                                                                                                                                                                    |
+---------------------------------+---------------------------------------------------------------------------------------------------+-----------------------------------+
| getRouteRefresh()               | Returns the route for refreshing the index route                                                  | /refresh_                         |
+---------------------------------+---------------------------------------------------------------------------------------------------+-----------------------------------+
| getRouteDetails()               | Returns the route for the detail route, when the user click on a dataTable row                    | /showDetail                       |
+---------------------------------+---------------------------------------------------------------------------------------------------+-----------------------------------+
| getRouteDelete()                | Returns the route for deleting an instance                                                        | /delete                           |
+---------------------------------+---------------------------------------------------------------------------------------------------+-----------------------------------+
| getRouteEdit()                  | Returns the route for editing an instance                                                         | /edit                             |
+---------------------------------+---------------------------------------------------------------------------------------------------+-----------------------------------+
| getRouteDisplay()               | Returns the route for displaying an instance                                                      | /display                          |
+---------------------------------+---------------------------------------------------------------------------------------------------+-----------------------------------+
| getRouteRefreshTable()          | Returns the route for refreshing the dataTable                                                    | /refreshTable                     |
+---------------------------------+---------------------------------------------------------------------------------------------------+-----------------------------------+
| getDetailClickURL($model)       | Returns the route associated with a foreign key instance in list                                  | ""                                |
+---------------------------------+---------------------------------------------------------------------------------------------------+-----------------------------------+

Twig Templates structure
^^^^^^^^^^^^^^^^^^^^^^^^

index.html
##########

.. image:: /_static/images/crud/template_index.png

form.html
#########

Displayed in **frm** block

.. image:: /_static/images/crud/template_form.png

display.html
############

Displayed in **frm** block

.. image:: /_static/images/crud/template_display.png
