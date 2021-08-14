<?php
namespace controllers;



/**
 * @rest("resource"=>"models\\Organization")
 * @route("path"=>"/rest/simple/orgas/")
 */
class TestRestSimpleOrga extends \Ubiquity\controllers\rest\RestResourceController {
	
	public function isValid($action) {
		return true;
	}
	
	/**
	 * Returns all links for this controller.
	 *
	 * @get("/links","priority"=>3000)
	 */
	public function index() {
		parent::index ();
	}
	
	/**
	 * Returns a list of objects from the server.
	 *
	 * @param string $condition the sql Where part
	 * @param boolean|string $included if true, loads associate members with associations, if string, example : client.*,commands
	 * @param boolean $useCache
	 * @get("list/{condition}/{included}/{useCache}", "priority"=> 0)
	 */
	public function all($condition = "1=1", $included = false, $useCache = false) {
		$this->_get ( $condition, $included, $useCache );
	}
	
	/**
	 * Get the first object corresponding to the $keyValues.
	 *
	 * @param string $keyValues primary key(s) value(s) or condition
	 * @param boolean|string $included if true, loads associate members with associations, if string, example : client.*,commands
	 * @param boolean $useCache if true then response is cached
	 * @get("{keyValues}/{included}/{useCache}", "priority"=> -1)
	 */
	public function one($keyValues, $included = false, $useCache = false) {
		$this->_getOne ( $keyValues, $included, $useCache );
	}
	
	/**
	 * Update an instance of $model selected by the primary key $keyValues.
	 * Require members values in $_POST array
	 * Requires an authorization with access token
	 *
	 * @param array $keyValues
	 * @authorization
	 * @put("{keyValues}")
	 */
	public function update(...$keyValues) {
		$this->_update ( ...$keyValues );
	}
	
	/**
	 * Insert a new instance of $model.
	 * Require members values in $_POST array
	 * Requires an authorization with access token
	 *
	 * @authorization
	 * @post("/")
	 */
	public function add() {
		$this->_add ();
	}
	
	/**
	 * Delete the instance of $model selected by the primary key $keyValues.
	 * Requires an authorization with access token
	 *
	 * @param array $keyValues
	 * @delete("{keyValues}")
	 * @authorization
	 */
	public function delete(...$keyValues) {
		$this->_delete ( ...$keyValues );
	}
	
	/**
	 * Route for CORS.
	 *
	 * @options("{resource}")
	 */
	public function options(...$resource) {}
}
