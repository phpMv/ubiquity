<?php

namespace Ubiquity\controllers\rest;

use Ubiquity\orm\DAO;
use Ubiquity\utils\base\UString;
use Ubiquity\utils\http\URequest;
use Ubiquity\contents\validation\ValidatorsManager;
use Ubiquity\contents\validation\validators\ConstraintViolation;
use Ubiquity\orm\OrmUtils;
use Ubiquity\orm\parser\Reflexion;

/**
 * Rest controller internal utilities.
 * Ubiquity\controllers\rest$RestControllerUtilitiesTrait
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.4
 * @property ResponseFormatter $responseFormatter
 * @property RestServer $server
 * @property string $model
 *
 */
trait RestControllerUtilitiesTrait {
	protected $errors;

	abstract public function _setResponseCode($value);

	protected function getDatas() {
		return URequest::getDatas ();
	}

	/**
	 *
	 * @param string $param
	 * @param string|boolean $default
	 * @return string|boolean
	 */
	protected function getRequestParam($param, $default) {
		if (isset ( $_GET [$param] )) {
			return $_GET [$param];
		}
		return $default;
	}

	protected function operate_($instance, $callback, $status, $exceptionMessage, $keyValues) {
		if (isset ( $instance )) {
			$result = $callback ( $instance );
			if ($result == true) {
				$formatter = $this->_getResponseFormatter ();
				echo $formatter->format ( [ "status" => $status,"data" => $formatter->cleanRestObject ( $instance ) ] );
			} elseif ($result === null) {
				$this->displayErrors ();
			} else {
				throw new \Exception ( $exceptionMessage );
			}
		} else {
			$this->_setResponseCode ( 404 );
			echo $this->_getResponseFormatter ()->format ( [ "message" => "No result found","keyValues" => $keyValues ] );
		}
	}

	protected function generatePagination(&$filter, $pageNumber, $pageSize) {
		$count = DAO::count ( $this->model, $filter );
		$pagesCount = ceil ( $count / $pageSize );
		$pages = [ 'self' => $pageNumber,'first' => 1,'last' => $pagesCount,'pageSize' => $pageSize ];
		if ($pageNumber - 1 > 0) {
			$pages ['prev'] = $pageNumber - 1;
		}
		if ($pageNumber + 1 <= $pagesCount) {
			$pages ['next'] = $pageNumber + 1;
		}
		$offset = ($pageNumber - 1) * $pageSize;
		$filter .= ' limit ' . $offset . ',' . $pageSize;
		return $pages;
	}

	protected function updateOperation($instance, $datas, $updateMany = false) {
		return DAO::update ( $instance, $updateMany );
	}

	protected function AddOperation($instance, $datas, $insertMany = false) {
		return DAO::insert ( $instance, $insertMany );
	}

	/**
	 *
	 * @return \Ubiquity\controllers\rest\ResponseFormatter
	 */
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

	/*
	 * protected function connectDb($config) {
	 * $db = $config ["database"];
	 * if ($db ["dbName"] !== "") {
	 * DAO::connect ( $db ["type"], $db ["dbName"], $db ["serverName"] ?? '127.0.0.1', $db ["port"] ?? 3306, $db ["user"] ?? 'root', $db ["password"] ?? '', $db ["options"] ?? [ ], $db ["cache"] ?? false);
	 * }
	 * }
	 */

	/**
	 * Updates $instance with $values
	 * To eventually be redefined in derived classes
	 *
	 * @param object $instance the instance to update
	 * @param array $values
	 */
	protected function _setValuesToObject($instance, $values = []) {
		if (URequest::isJSON ()) {
			if (is_string ( $values )) {
				$values = \json_decode ( $values, true );
			}
		}
		$className = \get_class ( $instance );
		$fieldsInRelationForUpdate = OrmUtils::getFieldsInRelationsForUpdate_ ( $className );
		$manyToOneRelations = $fieldsInRelationForUpdate ["manyToOne"];

		$members = array_keys ( $values );
		OrmUtils::setFieldToMemberNames ( $members, $fieldsInRelationForUpdate ["relations"] );
		URequest::setValuesToObject ( $instance, $values );
		if ($manyToOneRelations) {
			$this->updateManyToOne ( $manyToOneRelations, $members, $className, $instance, $values );
		}
	}

	protected function updateManyToOne($manyToOneRelations, $members, $className, $instance, $values) {
		foreach ( $manyToOneRelations as $member ) {
			if (array_search ( $member, $members ) !== false) {
				$joinColumn = OrmUtils::getAnnotationInfoMember ( $className, "#joinColumn", $member );
				if ($joinColumn) {
					$fkClass = $joinColumn ["className"];
					$fkField = $joinColumn ["name"];
					if (isset ( $values [$fkField] )) {
						$fkObject = DAO::getById ( $fkClass, $values ["$fkField"] );
						Reflexion::setMemberValue ( $instance, $member, $fkObject );
					}
				}
			}
		}
	}

	/**
	 *
	 * @param string|boolean $include
	 * @return array|boolean
	 */
	protected function getInclude($include) {
		if (! UString::isBooleanStr ( $include )) {
			return explode ( ",", $include );
		}
		return UString::isBooleanTrue ( $include );
	}

	protected function addError($code, $title, $detail = null, $source = null, $status = null) {
		$this->errors [] = new RestError ( $code, $title, $detail, $source, $status );
	}

	protected function hasErrors() {
		return is_array ( $this->errors ) && sizeof ( $this->errors ) > 0;
	}

	protected function displayErrors() {
		if ($this->hasErrors ()) {
			$status = 200;
			$errors = [ ];
			foreach ( $this->errors as $error ) {
				$errors [] = $error->asArray ();
				if ($error->getStatus () > $status) {
					$status = $error->getStatus ();
				}
			}
			echo $this->_getResponseFormatter ()->format ( [ 'errors' => $errors ] );
			$this->_setResponseCode ( $status );
			return true;
		}
		return false;
	}

	/**
	 *
	 * @param string $ids The primary key values (comma separated if pk is multiple)
	 * @param callable $getDatas
	 * @param string $member The member to load
	 * @param boolean|string $include if true, loads associate members with associations, if string, example : client.*,commands
	 * @param boolean $useCache
	 * @param boolean $multiple
	 * @throws \Exception
	 */
	protected function getAssociatedMemberValues_($ids, $getDatas, $member, $include = false, $useCache = false, $multiple = true) {
		$include = $this->getInclude ( $include );
		$useCache = UString::isBooleanTrue ( $useCache );
		$datas = $getDatas ( [ $this->model,$ids ], $member, $include, $useCache );
		if ($multiple) {
			echo $this->_getResponseFormatter ()->get ( $datas );
		} else {
			echo $this->_getResponseFormatter ()->getOne ( $datas );
		}
	}

	public function _validateInstance($instance, $members) {
		if ($this->useValidation) {
			$isValid = true;
			$violations = ValidatorsManager::validate ( $instance );
			foreach ( $violations as $violation ) {
				if (array_search ( $violation->getMember (), $members ) !== false) {
					$this->addViolation ( $violation );
					$isValid = false;
				}
			}
			return $isValid;
		}
		return true;
	}

	protected function addViolation(ConstraintViolation $violation) {
		$this->addError ( 406, 'Validation error', $violation->getMessage (), $violation->getMember (), $violation->getValidatorType () );
	}

	protected function getPrimaryKeysFromDatas($datas, $model) {
		$pks = OrmUtils::getKeyFields ( $model );
		$result = [ ];
		foreach ( $pks as $pk ) {
			if (isset ( $datas [$pk] )) {
				$result [] = $datas [$pk];
			} else {
				$this->addError ( 404, 'Primary key required', 'The primary key ' . $pk . ' is required!', $pk );
			}
		}
		return $result;
	}

	protected function getCondition($condition) {
		$condition = urldecode ( $condition );
		if (strpos ( $condition, 'like' ) !== false) {
			$condition = str_replace ( '*', '%', $condition );
		}
		return $condition;
	}
}

