CRUD Controllers
================


The CRUD controllers allow you to perform basic operations on a Model class:
 - Create
 - Read
 - Update
 - Delete
 - ...
 
 Creation
 ^^^^^^^^
 
 In the admin interface (web-tools), activate the **Controllers** part, and choose create **Crud controller**:

.. image:: /_static/images/crud/speControllerBtn.png

Then fill in the form:
  - Enter the controller name
  - Select the associated model
  - Then click on the validate button

.. image:: _static/images/crud/createCrudForm1.png

Description of features

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

.. image:: _static/images/crud/getBtn.png

.. image:: _static/images/crud/usersControllerIndex1.png
