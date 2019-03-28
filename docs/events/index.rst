.. _events:
Events
======

.. note::
   The Events module uses the static class **EventsManager** to manage events.
   

Framework core events
---------------------

Ubiquity emits events during the different phases of submitting a request. |br|
These events are relatively few in number, to limit their impact on performance.

+-----------------+-----------------+---------------------------+------------------------------------------------------------------------+
|Part             |Event name       | Parameters                |Occures when                                                            |
+=================+=================+===========================+========================================================================+
|ViewEvents       |BEFORE_RENDER    | viewname, parameters      |Before rendering a view                                                 |
+-----------------+-----------------+---------------------------+------------------------------------------------------------------------+
|ViewEvents       |AFTER_RENDER     | viewname, parameters      |After rendering a view                                                  |
+-----------------+-----------------+---------------------------+------------------------------------------------------------------------+
|DAOEvents        |GET_ALL          | objects, classname        |After loading multiple objects                                          |
+-----------------+-----------------+---------------------------+------------------------------------------------------------------------+
|DAOEvents        |GET_ONE          | object, classname         |After loading one object                                                |
+-----------------+-----------------+---------------------------+------------------------------------------------------------------------+
|DAOEvents        |UPDATE           | instance, result          |After updating an object                                                |
+-----------------+-----------------+---------------------------+------------------------------------------------------------------------+
|DAOEvents        |INSERT           | instance, result          |After inserting an object                                               |
+-----------------+-----------------+---------------------------+------------------------------------------------------------------------+

.. note::
   There is no **BeforeAction** and **AfterAction** event, since the **initialize** and **finalize** methods of the controller class perform this operation.

Listening to an event
---------------------
**Example 1 :**

Adding an **_updated** property on modified instances in the database :

.. code-block:: php
   :linenos:
   :caption: app/config/services.php
   
   use Ubiquity\events\EventsManager;
   use Ubiquity\events\DAOEvents;
   
   ...
   
	EventsManager::addListener(DAOEvents::AFTER_UPDATE, function($instance,$result){
		if($result==1){
			$instance->_updated=true;
		}
	});

.. note::
   The parameters passed to the callback function vary according to the event being listened to.

**Example 2 :**

Modification of the view rendering

.. code-block:: php
   :linenos:
   :caption: app/config/services.php
   
   use Ubiquity\events\EventsManager;
   use Ubiquity\events\ViewEvents;
   
   ...
   
	EventsManager::addListener(ViewEvents::AFTER_RENDER,function(&$render,$viewname,$datas){
		$render='<h1>'.$viewname.'</h1>'.$render;
	});

Creating your own events
------------------------

**Example :**

Creating an event to count and store the number of displays per action :

.. code-block:: php
   :linenos:
   :caption: app/eventListener/TracePageEventListener.php
   
	namespace eventListener;
	
	use Ubiquity\events\EventListenerInterface;
	use Ubiquity\utils\base\UArray;
	
	class TracePageEventListener implements EventListenerInterface {
		const EVENT_NAME = 'tracePage';
	
		public function on(&...$params) {
			$filename = \ROOT . \DS . 'config\stats.php';
			$stats = [ ];
			if (file_exists ( $filename )) {
				$stats = include $filename;
			}
			$page = $params [0] . '::' . $params [1];
			$value = $stats [$page] ?? 0;
			$value ++;
			$stats [$page] = $value;
			UArray::save ( $stats, $filename );
		}
	}

Registering events
------------------

Registering the **TracePageEventListener** event in ``services.php`` :

.. code-block:: php
   :linenos:
   :caption: app/config/services.php
   
	use Ubiquity\events\EventsManager;
	use eventListener\TracePageEventListener;
	
	...
	
	EventsManager::addListener(TracePageEventListener::EVENT_NAME, TracePageEventListener::class);

Triggering events
-----------------

An event can be triggered from anywhere, but it makes more sense to do it here in the **initialize** method of the base controller :

.. code-block:: php
   :linenos:
   :caption: app/controllers/ControllerBase.php
   :emphasize-lines: 16-18
   
	namespace controllers;
	
	use Ubiquity\controllers\Controller;
	use Ubiquity\utils\http\URequest;
	use Ubiquity\events\EventsManager;
	use eventListener\TracePageEventListener;
	use Ubiquity\controllers\Startup;
	
	/**
	 * ControllerBase.
	 **/
	abstract class ControllerBase extends Controller{
		protected $headerView = "@activeTheme/main/vHeader.html";
		protected $footerView = "@activeTheme/main/vFooter.html";
		public function initialize() {
			$controller=Startup::getController();
			$action=Startup::getAction();
			EventsManager::trigger(TracePageEventListener::EVENT_NAME, $controller,$action);
			if (! URequest::isAjax ()) {
				$this->loadView ( $this->headerView );
			}
		}
		public function finalize() {
			if (! URequest::isAjax ()) {
				$this->loadView ( $this->footerView );
			}
		}
	}

The result in app/config/stats.php :

.. code-block:: php
   :caption: app/config/stats.php
   
   return array(
		"controllers\\IndexController::index"=>5,
		"controllers\\IndexController::ct"=>1,
		"controllers\\NewController::index"=>1,
		"controllers\\TestUCookieController::index"=>1
	);
	
Events registering optimization
-------------------------------

It is preferable to cache the registration of listeners, to optimize their loading time :

Create a client script, or a controller action (not accessible in production mode) :

.. code-block:: php
   
	use Ubiquity\events\EventsManager;
   
	public function initEvents(){
		EventsManager::start();
		EventsManager::addListener(DAOEvents::AFTER_UPDATE, function($instance,$result){
			if($result==1){
				$instance->_updated=true;
			}
		});
		EventsManager::addListener(TracePageEventListener::EVENT_NAME, TracePageEventListener::class);
		EventsManager::store();
	}
	
After running, cache file is generated in ``app/cache/events/events.cache.php``.

Once the cache is created, the ``services.php`` file just needs to have the line :

.. code-block:: php
   
   \Ubiquity\events\EventsManager::start();


.. |br| raw:: html

   <br />