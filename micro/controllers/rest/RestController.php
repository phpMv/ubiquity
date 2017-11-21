<?php

namespace micro\controllers\rest;

use micro\controllers\Controller;
use micro\orm\DAO;
use micro\controllers\Startup;
use micro\utils\StrUtils;
use micro\cache\CacheManager;
use micro\utils\RequestUtils;

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
		@\set_exception_handler(array ($this,'_errorHandler' ));
		if(!\headers_sent()){
			$this->config=Startup::getConfig();
			$this->server=new RestServer($this->config);
			$this->server->cors();
			$this->responseFormatter=new ResponseFormatter();
			$this->contentType="application/json";
			$this->server->_setContentType($this->contentType);
			$this->restCache=CacheManager::getRestCacheController(\get_class($this));
		}
		parent::__construct();
	}

	public function isValid(){
		if(isset($this->restCache["authorizations"])){
			if(\array_search(Startup::getAction(), $this->restCache["authorizations"])!==false){
				return $this->server->isValid();
			}
		}
		return true;
	}

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
		$this->_setResponseCode(500);
		echo $this->responseFormatter->formatException($e);
	}

	public function _setResponseCode($value){
		\http_response_code($value);
	}

	protected function connectDb($config){
		$db=$config["database"];
		if($db["dbName"]!==""){
			DAO::connect($db["type"],$db["dbName"],@$db["serverName"],@$db["port"],@$db["user"],@$db["password"],@$db["cache"]);
		}
	}

	/**
	 * Updates $instance with $values
	 * To eventually be redefined in derived classes
	 * @param object $instance the instance to update
	 * @param array $values
	 */
	protected function _setValuesToObject($instance,$values){
		RequestUtils::setValuesToObject($instance,$values);
	}

	public function index() {
			$datas=DAO::getAll($this->model);
			$datas=\array_map(function($o){return $o->_rest;}, $datas);
			echo $this->responseFormatter->get($datas);
	}

	/**
	 * @param string $condition
	 * @param boolean $loadManyToOne
	 * @param boolean $loadOneToMany
	 * @param boolean $useCache
	 */
	public function get($condition="1=1",$loadManyToOne=false,$loadOneToMany=false,$useCache=false){
		try{
			$condition=\urldecode($condition);
			$loadManyToOne=StrUtils::isBooleanTrue($loadManyToOne);
			$loadOneToMany=StrUtils::isBooleanTrue($loadOneToMany);
			$useCache=StrUtils::isBooleanTrue($useCache);
			$datas=DAO::getAll($this->model,$condition,$loadManyToOne,$loadOneToMany,$useCache);
			$datas=\array_map(function($o){return $o->_rest;}, $datas);
			echo $this->responseFormatter->get($datas);
		}catch (\Exception $e){
			$this->_setResponseCode(500);
			echo $this->responseFormatter->formatException($e);
		}
	}

	/**
	 * @param string $keyValues
	 * @param boolean $loadManyToOne
	 * @param boolean $loadOneToMany
	 * @param boolean $useCache
	 */
	public function getOne($keyValues,$loadManyToOne=false,$loadOneToMany=false,$useCache=false){
		$keyValues=\urldecode($keyValues);
		$loadManyToOne=StrUtils::isBooleanTrue($loadManyToOne);
		$loadOneToMany=StrUtils::isBooleanTrue($loadOneToMany);
		$useCache=StrUtils::isBooleanTrue($useCache);
		$data=DAO::getOne($this->model, $keyValues,$loadManyToOne,$loadOneToMany,$useCache);
		if(isset($data)){
			$_SESSION["_restInstance"]=$data;
			echo $this->responseFormatter->getOne($data->_rest);
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
			$useCache=StrUtils::isBooleanTrue($useCache);
			$datas=DAO::getOneToMany($_SESSION["_restInstance"], $member,null,$useCache);
			$datas=\array_map(function($o){return $o->_rest;}, $datas);
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
			$useCache=StrUtils::isBooleanTrue($useCache);
			$datas=DAO::getManyToMany($_SESSION["_restInstance"], $member,null,$useCache);
			$datas=\array_map(function($o){return $o->_rest;}, $datas);
			echo $this->responseFormatter->get($datas);
		}else{
			throw new \Exception("You have to call getOne before calling getManyToMany.");
		}
	}

	/**
	 * Update an instance of $model selected by the primary key $keyValues
	 * @param $keyValues
	 * @authorization
	 */
	public function update(...$keyValues){
		$instance=DAO::getOne($this->model, $keyValues);
		if(isset($instance)){
			$this->_setValuesToObject($instance,RequestUtils::getInput());
			$result=DAO::update($instance);
			if($result){
				echo $this->responseFormatter->format(["status"=>"updated","data"=>$instance->_rest]);
			}else{
				throw new \Exception("Unable to update the instance");
			}
		}else{
			$this->_setResponseCode(404);
			echo $this->responseFormatter->format(["message"=>"No result found","keyValues"=>$keyValues]);
		}
	}
}
