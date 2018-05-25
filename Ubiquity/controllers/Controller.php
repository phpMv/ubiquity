<?php

/**
 * This file is part of Ubiquity framework
 *
 */
namespace Ubiquity\controllers;

use Ubiquity\views\View;
use Ubiquity\exceptions\RouterException;

/**
 * Base class for controllers
 *
 * @author jcheron
 * @version 1.0.3
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
	 * @param string $viewName
	 *        	view name to load
	 * @param mixed $pData
	 *        	Variable or associative array to pass to the view <br> If a variable is passed, it will have the name <b> $ data </ b> in the view, <br>
	 *        	If an associative array is passed, the view retrieves variables from the table's key names
	 * @param boolean $asString
	 *        	If true, the view is not displayed but returned as a string (usable in a variable)
	 * @throws \Exception
	 * @return string
	 */
	public function loadView($viewName, $pData = NULL, $asString = false) {
		if (isset ( $pData ))
			$this->view->setVars ( $pData );
		return $this->view->render ( $viewName, $asString );
	}
	
	/**
	 * Loads the default view (controllerName/actionName) possibly passing the variables $pdata
	 * @param mixed $pData
	 *        	Variable or associative array to pass to the view <br> If a variable is passed, it will have the name <b> $ data </ b> in the view, <br>
	 *        	If an associative array is passed, the view retrieves variables from the table's key names
	 * @param boolean $asString
	 *        	If true, the view is not displayed but returned as a string (usable in a variable)
	 * @throws \Exception
	 * @return string
	 */
	public function loadDefaultView($pData=NULL,$asString=false){
		return $this->loadView($this->getDefaultViewName(),$pData,$asString);
	}
	
	/**
	 * Returns the default view name for this controller/action i.e ControllerName/actionName.html for the action actionName in ControllerName
	 * @return string the default view name
	 */
	public function getDefaultViewName(){
		return Startup::getControllerSimpleName()."/".Startup::getAction().".".Startup::getViewNameFileExtension();
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
	 * Called if isValid () returns false <br>
	 * To be override in sub classes
	 */
	public function onInvalidControl() {
		\header ( 'HTTP/1.1 401 Unauthorized', true, 401 );
	}

	/**
	 * Loads the controller $controller and calls its $action method by passing the parameters $params
	 *
	 * @param string $controller
	 *        	The Controller
	 * @param string $action
	 *        	The action to call
	 * @param mixed $params
	 *        	Parameters passed to the $action method
	 * @param boolean $initialize
	 *        	If true, the controller's initialize method is called before $action
	 * @param boolean $finalize
	 *        	If true, the controller's finalize method is called after $action
	 * @throws \Exception
	 */
	public function forward($controller, $action = "index", $params = array(), $initialize = false, $finalize = false) {
		$u = array ($controller,$action );
		if (\is_array ( $params )) {
			$u = \array_merge ( $u, $params );
		} else {
			$u = \array_merge ( $u, [ $params ] );
		}
		return Startup::runAction ( $u, $initialize, $finalize );
	}

	/**
	 * Redirect to a route by its name
	 *
	 * @param string $routeName
	 * @param array $parameters
	 * @param boolean $initialize
	 * @param boolean $finalize
	 * @throws RouterException
	 */
	public function redirectToRoute($routeName, $parameters = [], $initialize = false, $finalize = false) {
		$path = Router::getRouteByName ( $routeName, $parameters );
		if ($path !== false) {
			$route = Router::getRoute ( $path, false );
			if ($route !== false) {
				return $this->forward ( $route [0], $route [1], \array_slice ( $route, 2 ), $initialize, $finalize );
			} else {
				throw new RouterException ( "Route {$routeName} not found", 404 );
			}
		} else {
			throw new RouterException ( "Route {$routeName} not found", 404 );
		}
	}

	/**
	 *
	 * @return \Ubiquity\views\View
	 */
	public function getView() {
		return $this->view;
	}
}
