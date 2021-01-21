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
For this example, create the following database:

.. code-block:: sql
   
   CREATE DATABASE `uguide` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
   USE `uguide`;
   
   CREATE TABLE `user` (
     `id` int(11) NOT NULL,
     `firstname` varchar(30) NOT NULL,
     `lastname` varchar(30) NOT NULL,
     `password` varchar(30) NOT NULL
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
   
   INSERT INTO `user` (`id`, `firstname`, `lastname`) VALUES
   (1, 'You', 'Evan'),
   (2, 'Potencier', 'Fabien'),
   (3, 'Otwell', 'Taylor');

   ALTER TABLE `user` ADD PRIMARY KEY (`id`);
   ALTER TABLE `user`
     MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
     
Connect the application to the database, and generate the `User` class:

With devtools:

.. code-block:: bash
   
   Ubiquity config:set --database.dbName=uguide
   Ubiquity all-models

Create a new Controller `UsersJqueryController`

.. code-block:: bash
   
   Ubiquity controller UsersJqueryController -v
   
Create the folowing actions in `UsersJqueryController`:

.. image:: /_static/images/richclient/semantic/UsersJqueryControllerStructure.png

Index action
############

The `index` action must display a button to obtain the list of users, loaded via an ajax request:

.. code-block:: php
   :linenos:
   :caption: app/controllers/UsersJqueryController.php
   
   namespace controllers;
   
   /**
    * Controller UsersJqueryController
    *
    * @property \Ajax\php\ubiquity\JsUtils $jquery
    * @route("users")
    */
   class UsersJqueryController extends ControllerBase {
   
   	/**
   	 *
   	 * {@inheritdoc}
   	 * @see \Ubiquity\controllers\Controller::index()
   	 * @get
   	 */
   	public function index() {
   		$this->jquery->getOnClick('#users-bt', Router::path('display.users'), '#users', [
   			'hasLoader' => 'internal'
   		]);
   		$this->jquery->renderDefaultView();
   	}
   }

The default view associated to `index` action:

.. code-block:: html
   :caption: app/views/UsersJqueryController/index.html
   
   <div class="ui container">
   	<div id="users-bt" class="ui button">
   		<i class="ui users icon"></i>
   		Display <b>users</b>
   	</div>
   	<p></p>
   	<div id="users">
   	</div>
   </div>
   {{ script_foot | raw }}


displayUsers action
###################
All users are displayed, and a click on a user must display the user details via a posted ajax request:

.. code-block:: php
   :linenos:
   :caption: app/controllers/UsersJqueryController.php
   :emphasize-lines: 11-27
   
   namespace controllers;
   
   /**
    * Controller UsersJqueryController
    *
    * @property \Ajax\php\ubiquity\JsUtils $jquery
    * @route("users")
    */
   class UsersJqueryController extends ControllerBase {
   ...
	/**
	 *
	 * @get("all","name"=>"display.users","cache"=>true)
	 */
	public function displayUsers() {
		$users = DAO::getAll(User::class);
		$this->jquery->click('#close-bt', '$("#users").html("");');
		$this->jquery->postOnClick('li[data-ajax]', Router::path('display.one.user', [
			""
		]), '{}', '#user-detail', [
			'attr' => 'data-ajax',
			'hasLoader' => false
		]);
		$this->jquery->renderDefaultView([
			'users' => $users
		]);
	}

The view associated to `displayUsers` action:

.. code-block:: html
   :caption: app/views/UsersJqueryController/displayUsers.html
   
   <div class="ui top attached header">
   	<i class="users circular icon"></i>
   	<div class="content">Users</div>
   </div>
   <div class="ui attached segment">
   	<ul id='users-content'>
   	{% for user in users %}
   		<li data-ajax="{{user.id}}">{{user.firstname }} {{user.lastname}}</li>
   	{% endfor %}
   	</ul>
   	<div id='user-detail'></div>
   </div>
   <div class="ui bottom attached inverted segment">
   <div id="close-bt" class="ui inverted button">Close</div>
   </div>
   {{ script_foot | raw }}


displayOneUser action
###################

.. code-block:: php
   :linenos:
   :caption: app/controllers/UsersJqueryController.php
   :emphasize-lines: 11-27
   
   namespace controllers;
   
   /**
    * Controller UsersJqueryController
    *
    * @property \Ajax\php\ubiquity\JsUtils $jquery
    * @route("users")
    */
   class UsersJqueryController extends ControllerBase {
   ...
   	/**
   	 *
   	 * @post("{userId}","name"=>"display.one.user","cache"=>true,"duration"=>3600)
   	 */
   	public function displayOneUser($userId) {
   		$user = DAO::getById(User::class, $userId);
   		$this->jquery->hide('#users-content', '', '', true);
   		$this->jquery->click('#close-user-bt', '$("#user-detail").html("");$("#users-content").show();');
   		$this->jquery->renderDefaultView([
   			'user' => $user
   		]);
   	}

The view associated to `displayOneUser` action:

.. code-block:: html
   :caption: app/views/UsersJqueryController/displayUsers.html
   
   <div class="ui label">
   	<i class="ui user icon"></i>
   	Id
   	<div class="detail">{{user.id}}</div>
   </div>
   <div class="ui label">
   	Firstname
   	<div class="detail">{{user.firstname}}</div>
   </div>
   <div class="ui label">
   	Lastname
   	<div class="detail">{{user.lastname}}</div>
   </div>
   <p></p>
   <div id="close-user-bt" class="ui black button">
   	<i class="ui users icon"></i>
   	Return to users
   </div>
   {{ script_foot | raw }}

Semantic components
-------------------

Next, we are going to make a controller implementing the same functionalities as before, but using **PhpMv-UI** components (Semantic part).

HtmlButton sample
+++++++++++++++++

Create a new Controller `UsersJqueryController`

.. code-block:: bash
   
   Ubiquity controller UsersCompoController -v
   
.. code-block:: php
   :linenos:
   :caption: app/controllers/UsersJqueryController.php
   
   namespace controllers;
   
   use Ubiquity\controllers\Router;

   /**
    * Controller UsersCompoController
    *
    * @property \Ajax\php\ubiquity\JsUtils $jquery
    * @route("users-compo")
    */
   class UsersCompoController extends ControllerBase {
   
   	private function semantic() {
   		return $this->jquery->semantic();
   	}
   
   	/**
   	 *
   	 * @get
   	 */
   	public function index() {
   		$bt = $this->semantic()->htmlButton('users-bt', 'Display users');
   		$bt->addIcon('users');
   		$bt->getOnClick(Router::path('display.compo.users'), '#users', [
   			'hasLoader' => 'internal'
   		]);
   		$this->jquery->renderDefaultView();
   	}


.. note::
   Calling renderView or renderDefaultView on the JQuery object performs the compilation of the component, and generates the corresponding HTML and JS.


The associated view integrates the button component with the `q` array available in the view :

.. code-block:: html
   :caption: app/views/UsersCompoController/index.html
   
   <div class="ui container">
   	{{ q['users-bt'] | raw }}
   	<p></p>
   	<div id="users">
   	</div>
   </div>
   {{ script_foot | raw }}

//todo
DataTable sample
+++++++++++++++++


