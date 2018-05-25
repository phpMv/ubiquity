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
	 * Realise the connection to the server
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
			$this->model=$modelsNS."\\".$this->responseFormatter->getModel($thisClass);
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
		echo $this->responseFormatter->formatException($e);
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
			echo $this->responseFormatter->get($datas);
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
	 * @param boolean $loadManyToOne
	 * @param boolean $loadOneToMany
	 * @param boolean $useCache
	 */
	public function get($condition="1=1",$loadManyToOne=false,$loadOneToMany=false,$useCache=false){
		try{
			$condition=\urldecode($condition);
			$loadManyToOne=UString::isBooleanTrue($loadManyToOne);
			$loadOneToMany=UString::isBooleanTrue($loadOneToMany);
			$useCache=UString::isBooleanTrue($useCache);
			$datas=DAO::getAll($this->model,$condition,$loadManyToOne,$loadOneToMany,$useCache);
			echo $this->responseFormatter->get($datas);
		}catch (\Exception $e){
			$this->_setResponseCode(500);
			echo $this->responseFormatter->formatException($e);
		}
	}

	/**
	 * Get the first object corresponding to the $keyValues
	 * @param string $keyValues primary key(s) value(s) or condition
	 * @param boolean $loadManyToOne if true then manyToOne members are loaded.
	 * @param boolean $loadOneToMany if true then oneToMany members are loaded.
	 * @param boolean $useCache if true then response is cached
	 */
	public function getOne($keyValues,$loadManyToOne=false,$loadOneToMany=false,$useCache=false){
		$keyValues=\urldecode($keyValues);
		$loadManyToOne=UString::isBooleanTrue($loadManyToOne);
		$loadOneToMany=UString::isBooleanTrue($loadOneToMany);
		$useCache=UString::isBooleanTrue($useCache);
		$data=DAO::getOne($this->model, $keyValues,$loadManyToOne,$loadOneToMany,$useCache);
		if(isset($data)){
			$_SESSION["_restInstance"]=$data;
			echo $this->responseFormatter->getOne($data);
		}
		else{
			$this->_setResponseCode(404);
			echo $this->responseFormatter->format(["message"=>"No result found","keyValues"=>$keyValues]);
		}
	}

	public function _format($arrayMessage){
		return $this->responseFormatter->format($arrayMessage);
	}

	/**
	 * @param string $member
	 * @param boolean $useCache
	 * @throws \Exception
	 */
	public function getOneToMany($member,$useCache=false){
		if(isset($_SESSION["_restInstance"])){
			$useCache=UString::isBooleanTrue($useCache);
			$datas=DAO::getOneToMany($_SESSION["_restInstance"], $member,$useCache);
			echo $this->responseFormatter->get($datas);
		}else{
			throw new \Exception("You have to call getOne before calling getOneToMany.");
		}
	}

	/**
	 * @param string $member
	 * @param boolean $useCache
	 * @throws \Exception
	 */
	public function getManyToMany($member,$useCache=false){
		if(isset($_SESSION["_restInstance"])){
			$useCache=UString::isBooleanTrue($useCache);
			$datas=DAO::getManyToMany($_SESSION["_restInstance"], $member,null,$useCache);
			echo $this->responseFormatter->get($datas);
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
				echo $this->responseFormatter->format(["status"=>"updated","data"=>$this->responseFormatter->cleanRestObject($instance)]);
			}else{
				throw new \Exception("Unable to update the instance");
			}
		}else{
			$this->_setResponseCode(404);
			echo $this->responseFormatter->format(["message"=>"No result found","keyValues"=>$keyValues]);
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
				echo $this->responseFormatter->format(["status"=>"inserted","data"=>$this->responseFormatter->cleanRestObject($instance)]);
			}else{
				throw new \Exception("Unable to insert the instance");
			}
		}else{
			$this->_setResponseCode(500);
			echo $this->responseFormatter->format(["message"=>"Unable to create ".$model." instance"]);
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
				echo $this->responseFormatter->format(["status"=>"deleted","data"=>$this->responseFormatter->cleanRestObject($instance)]);
			}else{
				throw new \Exception("Unable to delete the instance");
			}
		}else{
			$this->_setResponseCode(404);
			echo $this->responseFormatter->format(["message"=>"No result found","keyValues"=>$keyValues]);
		}
	}
}
