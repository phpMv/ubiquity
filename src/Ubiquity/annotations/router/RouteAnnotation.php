<?php

namespace Ubiquity\annotations\router;

use Ubiquity\annotations\BaseAnnotation;

/**
 * Defines a route.
 * usages :
 * - route("routePath") default path: ""
 * - route("path"=>"routePath")
 * - route("routePath","methods"=>["routeMethods"])
 * - route("routePath","cache"=>true,"duration"=>intValue)
 * - route("routePath","inherited"=>true)
 * - route("routePath","automated"=>true)
 * - route("routePath","requirements"=>["member"=>"regExpr"])
 * - route("routePath","priority"=>intValue)
 * - route("routePath","name"=>"routeName") default routeName: controllerName_actionName
 *
 * @usage('method'=>true,'class'=>true,'multiple'=>true, 'inherited'=>true)
 */
class RouteAnnotation extends BaseAnnotation {
	public $path;
	public $methods;
	public $name;
	public $cache;
	public $duration;
	public $inherited;
	public $automated;
	public $requirements;
	public $priority;

	/**
	 * Initialize the annotation.
	 */
	public function initAnnotation(array $properties) {
		$this->inherited = false;
		$this->automated = false;
		$this->requirements = [ ];
		$this->priority = 0;
		if (isset ( $properties [0] )) {
			$this->path = $properties [0];
			unset ( $properties [0] );
		} else
			$this->path = "";
		parent::initAnnotation ( $properties );
	}
}