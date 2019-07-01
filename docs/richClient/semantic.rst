jQuery Semantic-UI
******************

.. |br| raw:: html

   <br />

By default, Ubiquity uses the `phpMv-UI <https://phpmv-ui.kobject.net>`_ library for the client-rich part. |br|
**PhpMv-UI** allows to create components based on Semantic-UI or Bootstrap and to generate jQuery scripts in PHP.

This library is used for the **webtools** administration interface.

Integration
-----------

By default, a **$jquery** variable is injected in controllers at runtime.

This operation is done using dependency injection, in ``app/config.php``:

.. code-block:: php
   :caption: app/config.php
   
   ...
	"di"=>array(
			"@exec"=>array(
					"jquery"=>function ($controller){
						return \Ubiquity\core\Framework::diSemantic($controller);
						}
					)
			)
   ...

So there's nothing to do, |br|
but to facilitate its use and allow code completion in a controller, it is recommended to add the following code documentation:

.. code-block:: php
   :caption: app/controllers/FooController.php
   
    /**
    * Controller FooController
    * @property \Ajax\php\ubiquity\JsUtils $jquery
    **/
   class FooController extends ControllerBase{
   
   	public function index(){
   	}
   }

jQuery
------
Href to ajax requests
*********************

Create a new Controller and it's associated view, define the folowing routes:

.. code-block:: php
   :caption: app/controllers/FooController.php
   
   namespace controllers;

   /**
    *
    * @property \Ajax\php\ubiquity\JsUtils $jquery
    */
   class FooController extends ControllerBase {
   
   	public function index() {
   		$this->loadview("FooController/index.html");
   	}
   
   	/**
   	 *
   	 *@get("a","name"=>"action.a")
   	 */
   	public function aAction() {
   		echo "a";
   	}
   
   	/**
   	 *
   	 *@get("b","name"=>"action.b")
   	 */
   	public function bAction() {
   		echo "b";
   	}
   }
The associated view:

.. code-block:: html
   :caption: app/views/FooController/index.html
   
   	<a href="{{path('action.a')}}">Action a</a>
   	<a href="{{path('action.b')}}">Action b</a>

Initialize router cache and test this page in your browser at ``http://127.0.0.1:8090/FooController``:

.. code-block:: bash
   
   Ubiquity init:cache -t=controllers

transformation of requests into Ajax requests
+++++++++++++++++++++++++++++++++++++++++++++
The result of each ajax request should be displayed in an area of the page defined by its jQuery selector (``.result span``)

.. code-block:: php
   :caption: app/controllers/FooController.php
   
   namespace controllers;

   /**
    *
    * @property \Ajax\php\ubiquity\JsUtils $jquery
    */
   class FooController extends ControllerBase {
   
   	public function index() {
   		$this->jquery->getHref('a');
   		$this->jquery->renderView("FooController/index.html");
   	}
   	...
   }

In the folowing view, the **data-targer** attribute define the target of the ajax request (the span element in the element with the **result** css class). |br|
The ``script_foot`` is the generated jquery script produced by the renderView method.

.. code-block:: html
   :caption: app/views/FooController/index.html
   
   	<a href="{{path('action.a')}}" data-target=".result span">Action a</a>
   	<a href="{{path('action.b')}}" data-target=".result span">Action b</a>
   <div class='result'>
   	Action choisie :
   	<span>No One</span>
   </div>
   {{ script_foot | raw }}

Let's add a little css to make it more professional:

.. code-block:: html
   :caption: app/views/FooController/index.html
   
   <div class="ui buttons">
   	<a class="ui button" href="{{path('action.a')}}" data-target=".result span">Action a</a>
   	<a class="ui button" href="{{path('action.b')}}" data-target=".result span">Action b</a>
   </div>
   <div class='ui segment result'>
   	Action choisie :
   	<span class="ui label">No One</span>
   </div>
   {{ script_foot | raw }}
   

Semantic components
-------------------
