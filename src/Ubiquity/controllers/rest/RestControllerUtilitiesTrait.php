<?php

namespace Ubiquity\controllers\rest;

use Ubiquity\orm\DAO;
use Ubiquity\utils\base\UString;
use Ubiquity\utils\http\URequest;

/**
 *
 * @author jc
 * @property ResponseFormatter $responseFormatter
 * @property RestServer $server
 */
trait RestControllerUtilitiesTrait {

	abstract public function _setResponseCode($value);

	protected function operate_($instance, $callback, $status, $exceptionMessage, $keyValues) {
		if (isset ( $instance )) {
			$result = $callback ( $instance );
			if ($result) {
				$formatter = $this->_getResponseFormatter ();
				echo $formatter->format ( [ "status" => $status,"data" => $formatter->cleanRestObject ( $instance ) ] );
			} else {
				throw new \Exception ( $exceptionMessage );
			}
		} else {
			$this->_setResponseCode ( 404 );
			echo $this->_getResponseFormatter ()->format ( [ "message" => "No result found","keyValues" => $keyValues ] );
		}
	}
	
	protected function generatePagination(&$filter,$pageNumber,$pageSize){
			$count=DAO::count($this->model,$filter);
			$pagesCount=ceil($count/$pageSize);
			$pages=['self'=>$pageNumber,'first'=>1,'last'=>$pagesCount,'pageSize'=>$pageSize];
			if($pageNumber-1>0){
				$pages['prev']=$pageNumber-1;
			}
			if($pageNumber+1<=$pagesCount){
				$pages['next']=$pageNumber+1;
			}
			$offset=($pageNumber-1)*$pageSize;
			$filter.=' limit '.$offset.','.$pageSize;
			return $pages;
	}

	protected function _getResponseFormatter() {
		if (! isset ( $this->responseFormatter )) {
			$this->responseFormatter = $this->getResponseFormatter ();
		}
		return $this->responseFormatter;
	}

	/**
	 * To override, returns the active formatter for the response
	 *
	 * @return \Ubiquity\controllers\rest\ResponseFormatter
	 */
	protected function getResponseFormatter(): ResponseFormatter {
		return new ResponseFormatter ();
	}

	protected function _getRestServer() {
		if (! isset ( $this->server )) {
			$this->server = $this->getRestServer ();
		}
		return $this->server;
	}

	/**
	 * To override, returns the active RestServer
	 *
	 * @return \Ubiquity\controllers\rest\RestServer
	 */
	protected function getRestServer(): RestServer {
		return new RestServer ( $this->config );
	}

	protected function connectDb($config) {
		$db = $config ["database"];
		if ($db ["dbName"] !== "") {
			DAO::connect ( $db ["type"], $db ["dbName"], @$db ["serverName"], @$db ["port"], @$db ["user"], @$db ["password"], @$db ["options"], @$db ["cache"] );
		}
	}

	/**
	 * Updates $instance with $values
	 * To eventually be redefined in derived classes
	 *
	 * @param object $instance
	 *        	the instance to update
	 * @param array|null $values
	 */
	protected function _setValuesToObject($instance, $values = null) {
		if (URequest::isJSON ()) {
			$values = \json_decode ( $values, true );
		}
		URequest::setValuesToObject ( $instance, $values );
	}

	/**
	 *
	 * @param string|boolean $included
	 * @return array|boolean
	 */
	protected function getIncluded($included) {
		if (! UString::isBooleanStr ( $included )) {
			return explode ( ",", $included );
		}
		return UString::isBooleanTrue ( $included );
	}

	/**
	 *
	 * @param callable $getDatas
	 * @param string $member
	 * @param boolean|string $included
	 *        	if true, loads associate members with associations, if string, example : client.*,commands
	 * @param boolean $useCache
	 * @throws \Exception
	 */
	protected function getMany_($getDatas, $member, $included = false, $useCache = false) {
		if (isset ( $_SESSION ["_restInstance"] )) {
			$included = $this->getIncluded ( $included );
			$useCache = UString::isBooleanTrue ( $useCache );
			$datas = $getDatas ( $_SESSION ["_restInstance"], $member, $included, $useCache );
			echo $this->_getResponseFormatter ()->get ( $datas );
		} else {
			throw new \Exception ( "You have to call getOne before calling getManyToMany or getOneToMany." );
		}
	}
}

