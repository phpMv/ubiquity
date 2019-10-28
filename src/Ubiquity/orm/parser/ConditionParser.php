<?php

namespace Ubiquity\orm\parser;

use Ubiquity\orm\OrmUtils;
use Ubiquity\db\SqlUtils;

/**
 * Represents a query condition.
 *
 * Ubiquity\orm\parser$ConditionParser
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.2
 *
 */
class ConditionParser {
	private $firstPart;
	private $condition;
	private $parts = [ ];
	private $params;
	private $invertedParams = true;

	public function __construct($condition = null, $firstPart = null, $params = null) {
		$this->condition = $condition;
		$this->firstPart = $firstPart;
		if (\is_array ( $params )) {
			$this->setParams ( $params );
		}
	}

	public function addKeyValues($keyValues, $classname, $separator = ' AND ') {
		if (! \is_array ( $keyValues )) {
			$this->condition = $this->parseKey ( $keyValues, $classname );
		} else {
			if ((\array_keys ( $keyValues ) === \range ( 0, \count ( $keyValues ) - 1 ))) { // Not associative array
				if (isset ( $classname )) {
					$keys = OrmUtils::getKeyFields ( $classname );
					if (\is_array ( $keys )) {
						$keyValues = \array_combine ( $keys, $keyValues );
					}
				}
			}
			$retArray = array ();
			foreach ( $keyValues as $key => $value ) {
				if ($this->addParams ( $value )) {
					$retArray [] = SqlUtils::$quote . $key . SqlUtils::$quote . ' = ?';
				}
			}
			$this->condition = \implode ( $separator, $retArray );
		}
	}

	public function setKeyValues($values) {
		if (! \is_array ( $values )) {
			$this->params = [ $values => true ];
		} else {
			$this->params = [ ];
			foreach ( $values as $val ) {
				$this->params [$val] = true;
			}
		}
	}

	private function addParams($value) {
		if (! isset ( $this->params [$value] )) {
			return $this->params [$value] = true;
		}
		return false;
	}

	public function addPart($condition, $value) {
		if ($this->addParams ( $value )) {
			$this->parts [] = $condition;
			return true;
		}
		return false;
	}

	public function addParts($condition, $values) {
		foreach ( $values as $value ) {
			if ($this->addParams ( $value )) {
				$this->parts [] = $condition;
			}
		}
	}

	public function compileParts($separator = ' OR ') {
		if ($separator == ' OR ' && \sizeof ( $this->parts ) > 3) {
			$parts = $this->refactorParts ();
			$conditions = [ ];
			foreach ( $parts as $part => $values ) {
				$values [0] = 'SELECT ? as _id';
				$conditions [] = ' INNER JOIN (' . \implode ( ' UNION ALL SELECT ', $values ) . ') as _tmp ON ' . $part . '=_tmp._id';
			}
			$this->condition = \implode ( ' ', $conditions );
		} else {
			$this->condition = \implode ( $separator, $this->parts );
		}
	}

	private function refactorParts() {
		$result = [ ];
		foreach ( $this->parts as $part ) {
			$part = \str_replace ( '= ?', '', $part );
			$result [$part] [] = '?';
		}
		return $result;
	}

	private function parseKey($keyValues, $className) {
		$condition = $keyValues;
		if (\strrpos ( $keyValues, '=' ) === false && \strrpos ( $keyValues, '>' ) === false && \strrpos ( $keyValues, '<' ) === false) {
			if ($this->addParams ( $keyValues )) {
				$condition = SqlUtils::$quote . OrmUtils::getFirstKey ( $className ) . SqlUtils::$quote . '= ?';
			}
		}
		return $condition;
	}

	/**
	 *
	 * @return string
	 */
	public function getCondition() {
		if ($this->firstPart == null)
			return $this->condition;
		$ret = $this->firstPart;
		if (isset ( $this->condition )) {
			$ret .= ' WHERE ' . $this->condition;
		}
		return $ret;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getParams() {
		if (\is_array ( $this->params )) {
			if ($this->invertedParams) {
				return \array_keys ( $this->params );
			}
			return $this->params;
		}
		return;
	}

	/**
	 *
	 * @return mixed
	 */
	public function hasParam($value) {
		if (\is_array ( $this->params )) {
			if ($this->invertedParams) {
				return isset ( $this->params [$value] );
			}
			return \array_search ( $value, $this->params ) !== false;
		}
		return false;
	}

	public function countParts() {
		if (\is_array ( $this->params ))
			return \sizeof ( $this->params );
		return 0;
	}

	/**
	 *
	 * @param string $condition
	 */
	public function setCondition($condition) {
		$this->condition = $condition;
		return $this;
	}

	/**
	 *
	 * @param mixed $params
	 */
	public function setParams($params) {
		$this->params = $params;
		$this->invertedParams = false;
		return $this;
	}

	public function limitOne() {
		$limit = '';
		if (\stripos ( $this->condition, ' limit ' ) === false) {
			$limit = ' limit 1';
		}
		$this->condition .= $limit;
	}

	public static function simple($condition, $params) {
		$cParser = new ConditionParser ( $condition );
		$cParser->addParams ( $params );
		return $cParser;
	}
}

