.. _richclient:
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
   
   	public function index(){}
   }

jQuery
------
Href to ajax requests
+++++++++++++++++++++

Create a new Controller and its associated view, then define the folowing routes:

.. code-block:: php
   :linenos:
   :caption: app/controllers/FooController.php
   
   namespace controllers;

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

Initialize router cache:

.. code-block:: bash
   
   Ubiquity init:cache -t=controllers

Test this page in your browser at ``http://127.0.0.1:8090/FooController``.

Transformation of requests into Ajax requests
#############################################

The result of each ajax request should be displayed in an area of the page defined by its jQuery selector (``.result span``)

.. code-block:: php
   :caption: app/controllers/FooController.php
   
   namespace controllers;

   /**
    * @property \Ajax\php\ubiquity\JsUtils $jquery
    */
   class FooController extends ControllerBase {
   
   	public function index() {
   		$this->jquery->getHref('a','.result span');
   		$this->jquery->renderView("FooController/index.html");
   	}
   	...
   }


.. code-block:: html
   :emphasize-lines: 7
   :caption: app/views/FooController/index.html
   
   	<a href="{{path('action.a')}}">Action a</a>
   	<a href="{{path('action.b')}}">Action b</a>
   <div class='result'>
   	Selected action:
   	<span>No One</span>
   </div>
   {{ script_foot | raw }}

.. note:: The ``script_foot`` variable contains the generated jquery script produced by the **renderView** method.
   The **raw** filter marks the value as being "safe", which means that in an environment with automatic escaping enabled this variable will not be escaped.

Let's add a little css to make it more professional:

.. code-block:: html
   :caption: app/views/FooController/index.html
   
   <div class="ui buttons">
   	<a class="ui button" href="{{path('action.a')}}">Action a</a>
   	<a class="ui button" href="{{path('action.b')}}">Action b</a>
   </div>
   <div class='ui segment result'>
   	Selected action:
   	<span class="ui label">No One</span>
   </div>
   {{ script_foot | raw }}
   

If we want to add a new link whose result should be displayed in another area, it is possible to specify it via the **data-target** attribute

The new action:

.. code-block:: php
   :caption: app/controllers/FooController.php
   
   namespace controllers;

   class FooController extends ControllerBase {
   	...
   	/**
   	 *@get("c","name"=>"action.c")
   	 */
   	public function cAction() {
   		echo \rand(0, 1000);
   	}
   }
The associated view:

.. code-block:: html
   :emphasize-lines: 4,9
   :caption: app/views/FooController/index.html
   
   <div class="ui buttons">
   	<a class="ui button" href="{{path('action.a')}}">Action a</a>
   	<a class="ui button" href="{{path('action.b')}}">Action b</a>
   	<a class="ui button" href="{{path('action.c')}}" data-target=".result p">Action c</a>
   </div>
   <div class='ui segment result'>
   	Selected action:
   	<span class="ui label">No One</span>
   	<p></p>
   </div>
   {{ script_foot | raw }}

.. image:: /_static/images/richclient/semantic/fooController.png
   :class: bordered

Definition of the ajax request attributes:
##########################################

In the folowing example, the parameters passed to the **attributes** variable of the ``getHref`` method:

 - remove the history of the navigation,
 - make the ajax loader internal to the clicked button.

.. code-block:: php
   :linenos:
   :emphasize-lines: 10-11
   :caption: app/controllers/FooController.php
   
   namespace controllers;

   /**
    * @property \Ajax\php\ubiquity\JsUtils $jquery
    */
   class FooController extends ControllerBase {
   
   	public function index() {
   		$this->jquery->getHref('a','.result span', [
   			'hasLoader' => 'internal',
   			'historize' => false
   		]);
   		$this->jquery->renderView("FooController/index.html");
   	}
   	...
   }

.. note:: It is possible to use the ``postHref`` method to use the **POST** http method.

Classical ajax requests
+++++++++++++++++++++++



Semantic components
-------------------

HtmlButton sample
+++++++++++++++++



