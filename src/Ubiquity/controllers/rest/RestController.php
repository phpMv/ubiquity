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
	use RestControllerUtilitiesTrait;
	
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

	/**
	 * Returns all objects for the resource $model
	 * @route("cache"=>false,"priority"=>100)
	 */
	public function index() {
			$datas=DAO::getAll($this->model);
			echo $this->_getResponseFormatter()->get($datas);
	}

	/**
	 * Default route for requiring a single object
	 * @route("{id}","methods"=>["get","options"],"requirements"=>["id"=>"[0-9]+"])
	 */
	public function getById($id){
		$this->getOne($id,true,false);
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
			$datas=DAO::getAll($this->model,$condition,$included,null,$useCache);
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
		$data=DAO::getOne($this->model, $keyValues,$included,null,$useCache);
		if(isset($data)){
			$_SESSION["_restInstance"]=$data;
			echo $this->_getResponseFormatter()->getOne($data);
		}
		else{
			$this->_setResponseCode(404);
			echo $this->_getResponseFormatter()->format(["message"=>"No result found","keyValues"=>$keyValues]);
		}
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
		$this->getMany_(function($instance,$member,$included,$useCache){
			return DAO::getOneToMany($instance, $member,$included,$useCache);
		}, $member,$included,$useCache);
	}

	/**
	 * @param string $member
	 * @param boolean|string $included if true, loads associate members with associations, if string, example : client.*,commands
	 * @param boolean $useCache
	 * @throws \Exception
	 */
	public function getManyToMany($member,$included=false,$useCache=false){
		$this->getMany_(function($instance,$member,$included,$useCache){
			return DAO::getManyToMany($instance, $member,$included,null,$useCache);
		}, $member,$included,$useCache);
	}
	
	/**
	 * Update an instance of $model selected by the primary key $keyValues
	 * Require members values in $_POST array
	 * @param array $keyValues
	 * @authorization
	 */
	public function update(...$keyValues){
		$instance=DAO::getOne($this->model, $keyValues);
		$this->operate_($instance, function($instance){
			$this->_setValuesToObject($instance,URequest::getDatas());
			return DAO::update($instance);
		}, "updated", "Unable to update the instance", $keyValues);
	}

	/**
	 * Insert a new instance of $model
	 * Require members values in $_POST array
	 * @authorization
	 */
	public function add(){
		$model=$this->model;
		$instance=new $model();
		$this->operate_($instance, function($instance){
			$this->_setValuesToObject($instance,URequest::getDatas());
			return DAO::insert($instance);
		}, "inserted", "Unable to insert the instance", []);
	}

	/**
	 * Delete the instance of $model selected by the primary key $keyValues
	 * Requires an authorization with access token
	 * @param array $keyValues
	 * @route("methods"=>["delete"],"priority"=>30)
	 * @authorization
	 */
	public function delete(...$keyValues){
		$instance=DAO::getOne($this->model, $keyValues);
		$this->operate_($instance, function($instance){
			return DAO::remove($instance);
		}, "deleted", "Unable to delete the instance", $keyValues);
	}
}
