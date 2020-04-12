<?php

namespace Ubiquity\controllers;

use Ubiquity\views\View;
use Ubiquity\exceptions\RouterException;
use Ubiquity\themes\ThemesManager;

/**
 * Base class for controllers.
 * Ubiquity\controllers$Controller
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.5
 *
 */
abstract class Controller {

	/**
	 * The view
	 *
	 * @var View
	 */
	protected $view;

	/**
	 * Default action
	 */
	abstract public function index();

	/**
	 * Constructor
	 * initialize $view variable
	 */
	public function __construct() {
		$this->view = new View ();
	}

	/**
	 * Method called before each action
	 * Can be override in derived class
	 */
	public function initialize() {
	}

	/**
	 * Method called after each action
	 * Can be override in derived class
	 */
	public function finalize() {
	}

	/**
	 * Loads the view $viewName possibly passing the variables $pdata
	 *
	 * @param string $viewName The name of the view to load
	 * @param mixed $pData Variable or associative array to pass to the view
	 *        If a variable is passed, it will have the name **$data** in the view,
	 *        If an associative array is passed, the view retrieves variables from the table's key names
	 * @param boolean $asString If true, the view is not displayed but returned as a string (usable in a variable)
	 * @throws \Exception
	 * @return string null or the view content if **$asString** parameter is true
	 */
	public function loadView($viewName, $pData = NULL, $asString = false) {
		if (isset ( $pData )) {
			$this->view->setVars ( $pData );
		}
		return $this->view->render ( $viewName, $asString );
	}

	/**
	 * Loads the default view (controllerName/actionName) possibly passing the variables $pdata.
	 * (@activeTheme/controllerName/actionName if themes are activated)
	 *
	 * @param mixed $pData Variable or associative array to pass to the view
	 *        If a variable is passed, it will have the name **$data** in the view,
	 *        If an associative array is passed, the view retrieves variables from the table's key names
	 * @param boolean $asString If true, the view is not displayed but returned as a string (usable in a variable)
	 * @throws \Exception
	 * @return string null or the view content if **$asString** parameter is true
	 */
	public function loadDefaultView($pData = NULL, $asString = false) {
		return $this->loadView ( $this->getDefaultViewName (), $pData, $asString );
	}

	/**
	 * Returns the default view name for this controller/action i.e ControllerName/actionName.html for the action actionName in ControllerName
	 * If there is an activeTheme **@activeTheme/ControllerName/actionName.html**
	 *
	 * @return string the default view name
	 */
	public function getDefaultViewName() {
		$activeTheme = ThemesManager::getActiveTheme ();
		if ($activeTheme !== '') {
			return '@activeTheme/' . Startup::getControllerSimpleName () . "/" . Startup::getAction () . "." . Startup::getViewNameFileExtension ();
		}
		return Startup::getControllerSimpleName () . "/" . Startup::getAction () . "." . Startup::getViewNameFileExtension ();
	}

	/**
	 * Returns True if access to the controller is allowed
	 * To be override in sub classes
	 *
	 * @param string $action
	 * @return boolean
	 */
	public function isValid($action) {
		return true;
	}

	/**
	 * Called if isValid () returns false
	 * To be override in sub classes
	 */
	public function onInvalidControl() {
		if (! headers_sent ()) {
			\header ( 'HTTP/1.1 401 Unauthorized', true, 401 );
		}
	}

	/**
	 * Loads the controller $controller and calls its $action method by passing the parameters $params
	 *
	 * @param string $controller The Controller
	 * @param string $action The action to call
	 * @param mixed $params Parameters passed to the **$action** method
	 * @param boolean $initialize If true, the controller's initialize method is called before $action
	 * @param boolean $finalize If true, the controller's finalize method is called after $action
	 * @throws \Exception
	 */
	public function forward($controller, $action = "index", $params = array (), $initialize = false, $finalize = false) {
		$u = array ($controller,$action );
		if (\is_array ( $params )) {
			$u = \array_merge ( $u, $params );
		} else {
			$u = \array_merge ( $u, [ $params ] );
		}
		Startup::runAction ( $u, $initialize, $finalize );
	}

	/**
	 * Redirect to a route by its name
	 *
	 * @param string $routeName The route name
	 * @param array $parameters The parameters to pass to the route action
	 * @param boolean $initialize Call the **initialize** method if true
	 * @param boolean $finalize Call the **finalize** method if true
	 * @throws RouterException
	 */
	public function redirectToRoute($routeName, $parameters = [ ], $initialize = false, $finalize = false) {
		$infos = Router::getRouteInfoByName ( $routeName );
		if ($infos !== false) {
			if (isset ( $infos ['controller'] )) {
				$this->forward ( $infos ['controller'], $infos ['action'] ?? 'index', $parameters, $initialize, $finalize );
			} else {
				$method = \strtolower ( $_SERVER ['REQUEST_METHOD'] );
				if (isset ( $infos [$method] )) {
					$infos = $infos [$method];
					$this->forward ( $infos ['controller'], $infos ['action'] ?? 'index', $parameters, $initialize, $finalize );
				} else {
					throw new RouterException ( "Route {$routeName} not found for method {$method}", 404 );
				}
			}
		} else {
			throw new RouterException ( "Route {$routeName} not found", 404 );
		}
	}

	/**
	 * Returns the associated view instance
	 *
	 * @return \Ubiquity\views\View
	 */
	public function getView() {
		return $this->view;
	}
}
