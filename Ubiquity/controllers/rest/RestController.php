<?php

namespace Ubiquity\controllers\rest;

use Ubiquity\controllers\Controller;
use Ubiquity\orm\DAO;
use Ubiquity\controllers\Startup;
use Ubiquity\utils\base\UString;
use Ubiquity\cache\CacheManager;
use Ubiquity\utils\http\URequest;

/**
 * @author jc
 * Abstract base class for Rest controllers
 *
 */
abstract class RestController extends Controller {
	protected $config;
	protected $model;
	protected $contentType;
	protected $restCache;
	/**
	 * @var ResponseFormatter
	 */
	protected $responseFormatter;

	/**
	 * @var RestServer
	 */
	protected $server;

	public function __construct(){
		if(!\headers_sent()){
			@\set_exception_handler(array ($this,'_errorHandler' ));
			$this->config=Startup::getConfig();
			$this->server=new RestServer($this->config);
			$this->server->cors();
			$this->responseFormatter=new ResponseFormatter();
			$this->contentType="application/json";
			$this->server->_setContentType($this->contentType);
			$this->restCache=CacheManager::getRestCacheController(\get_class($this));
		}
		if (! $this->isValid (Startup::getAction()))
			$this->onInvalidControl ();
	}

	public function isValid($action){
		if(isset($this->restCache["authorizations"])){
			if(\array_search($action, $this->restCache["authorizations"])!==false){
				return $this->server->isValid();
			}
		}
		return true;
	}

	public function onInvalidControl(){
		throw new \Exception('HTTP/1.1 401 Unauthorized, you need an access token for this request',401);
	}

	/**
	 * Realize the connection to the server
	 * To override in derived classes to define your own authentication
	 */
	public function connect(){
		$this->server->connect($this);
	}
	
	protected function _getResponseFormatter(){
		if(!isset($this->responseFormatter)){
			$this->responseFormatter=$this->getResponseFormatter();
		}
		return $this->responseFormatter;
	}
	
	/**
	 * To override, returns the active formatter for the response
	 * @return \Ubiquity\controllers\rest\ResponseFormatter
	 */
	protected function getResponseFormatter(){
		return new ResponseFormatter();
	}

	public function initialize(){
		$thisClass=\get_class($this);
		if(!isset($this->model))
			$this->model=CacheManager::getRestResource($thisClass);
		if(!isset($this->model)){
			$modelsNS=$this->config["mvcNS"]["models"];
			$this->model=$modelsNS."\\".$this->_getResponseFormatter()->getModel($thisClass);
		}
		$this->connectDb($this->config);
	}

	public function finalize(){
		parent::finalize();
		$this->server->finalizeTokens();
	}



	public function _errorHandler($e){
		$code=500;
		if($e->getCode()!==0)
			$code=$e->getCode();
		$this->_setResponseCode($code);
		echo $this->_getResponseFormatter()->formatException($e);
	}

	public function _setResponseCode($value){
		\http_response_code($value);
	}

	protected function connectDb($config){
		$db=$config["database"];
		if($db["dbName"]!==""){
			DAO::connect($db["type"],$db["dbName"],@$db["serverName"],@$db["port"],@$db["user"],@$db["password"],@$db["options"],@$db["cache"]);
		}
	}

	/**
	 * Updates $instance with $values
	 * To eventually be redefined in derived classes
	 * @param object $instance the instance to update
	 * @param array|null $values
	 */
	protected function _setValuesToObject($instance,$values=null){
		if(URequest::isJSON()){
			$values=\json_decode($values,true);
		}
		URequest::setValuesToObject($instance,$values);
	}

	/**
	 * Returns all objects for the resource $model
	 * @route("cache"=>false)
	 */
	public function index() {
			$datas=DAO::getAll($this->model);
			echo $this->_getResponseFormatter()->get($datas);
	}

	/**
	 * Default route for requiring a single object
	 * @route("{id}","methods"=>["get","options"])
	 */
	public function getById($id){
		return $this->getOne($id,true,true);
	}

	/**
	 * Returns a list of objects from the server
	 * @param string $condition the sql Where part
	 * @param boolean|string $included if true, loads associate members with associations, if string, example : client.*,commands
	 * @param boolean $useCache
	 */
	public function get($condition="1=1",$included=false,$useCache=false){
		try{
			$condition=\urldecode($condition);
			$included=$this->getIncluded($included);
			$useCache=UString::isBooleanTrue($useCache);
			$datas=DAO::getAll($this->model,$condition,$included,$useCache);
			echo $this->_getResponseFormatter()->get($datas);
		}catch (\Exception $e){
			$this->_setResponseCode(500);
			echo $this->_getResponseFormatter()->formatException($e);
		}
	}

	/**
	 * Get the first object corresponding to the $keyValues
	 * @param string $keyValues primary key(s) value(s) or condition
	 * @param boolean|string $included if true, loads associate members with associations, if string, example : client.*,commands
	 * @param boolean $useCache if true then response is cached
	 */
	public function getOne($keyValues,$included=false,$useCache=false){
		$keyValues=\urldecode($keyValues);
		$included=$this->getIncluded($included);
		$useCache=UString::isBooleanTrue($useCache);
		$data=DAO::getOne($this->model, $keyValues,$included,$useCache);
		if(isset($data)){
			$_SESSION["_restInstance"]=$data;
			echo $this->_getResponseFormatter()->getOne($data);
		}
		else{
			$this->_setResponseCode(404);
			echo $this->_getResponseFormatter()->format(["message"=>"No result found","keyValues"=>$keyValues]);
		}
	}
	
	private function getIncluded($included){
		if(!UString::isBoolean($included)){
			return explode(",", $included);
		}
		return UString::isBooleanTrue($included);
	}

	public function _format($arrayMessage){
		return $this->_getResponseFormatter()->format($arrayMessage);
	}

	/**
	 * @param string $member
	 * @param boolean|string $included if true, loads associate members with associations, if string, example : client.*,commands
	 * @param boolean $useCache
	 * @throws \Exception
	 */
	public function getOneToMany($member,$included=false,$useCache=false){
		if(isset($_SESSION["_restInstance"])){
			$included=$this->getIncluded($included);
			$useCache=UString::isBooleanTrue($useCache);
			$datas=DAO::getOneToMany($_SESSION["_restInstance"], $member,$included,$useCache);
			echo $this->_getResponseFormatter()->get($datas);
		}else{
			throw new \Exception("You have to call getOne before calling getOneToMany.");
		}
	}

	/**
	 * @param string $member
	 * @param boolean|string $included if true, loads associate members with associations, if string, example : client.*,commands
	 * @param boolean $useCache
	 * @throws \Exception
	 */
	public function getManyToMany($member,$included=false,$useCache=false){
		if(isset($_SESSION["_restInstance"])){
			$included=$this->getIncluded($included);
			$useCache=UString::isBooleanTrue($useCache);
			$datas=DAO::getManyToMany($_SESSION["_restInstance"], $member,$included,null,$useCache);
			echo $this->_getResponseFormatter()->get($datas);
		}else{
			throw new \Exception("You have to call getOne before calling getManyToMany.");
		}
	}

	/**
	 * Update an instance of $model selected by the primary key $keyValues
	 * Require members values in $_POST array
	 * @param array $keyValues
	 * @authorization
	 */
	public function update(...$keyValues){
		$instance=DAO::getOne($this->model, $keyValues);
		if(isset($instance)){
			$this->_setValuesToObject($instance,URequest::getInput());
			$result=DAO::update($instance);
			if($result){
				$formatter=$this->_getResponseFormatter();
				echo $formatter->format(["status"=>"updated","data"=>$formatter->cleanRestObject($instance)]);
			}else{
				throw new \Exception("Unable to update the instance");
			}
		}else{
			$this->_setResponseCode(404);
			echo $this->_getResponseFormatter()->format(["message"=>"No result found","keyValues"=>$keyValues]);
		}
	}

	/**
	 * Insert a new instance of $model
	 * Require members values in $_POST array
	 * @authorization
	 */
	public function add(){
		$model=$this->model;
		$instance=new $model();
		if(isset($instance)){
			$this->_setValuesToObject($instance,URequest::getInput());
			$result=DAO::insert($instance);
			if($result){
				$formatter=$this->_getResponseFormatter();
				echo $formatter->format(["status"=>"inserted","data"=>$formatter->cleanRestObject($instance)]);
			}else{
				throw new \Exception("Unable to insert the instance");
			}
		}else{
			$this->_setResponseCode(500);
			echo $this->_getResponseFormatter()->format(["message"=>"Unable to create ".$model." instance"]);
		}
	}

	/**
	 * Delete the instance of $model selected by the primary key $keyValues
	 * Requires an authorization with access token
	 * @param array $keyValues
	 * @route("methods"=>["delete"])
	 * @authorization
	 */
	public function delete(...$keyValues){
		$instance=DAO::getOne($this->model, $keyValues);
		if(isset($instance)){
			$result=DAO::remove($instance);
			if($result){
				$formatter=$this->_getResponseFormatter();
				echo $formatter->format(["status"=>"deleted","data"=>$formatter->cleanRestObject($instance)]);
			}else{
				throw new \Exception("Unable to delete the instance");
			}
		}else{
			$this->_setResponseCode(404);
			echo $this->_getResponseFormatter()->format(["message"=>"No result found","keyValues"=>$keyValues]);
		}
	}
}
