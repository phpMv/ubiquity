<?php

namespace Ubiquity\orm\core\prepared;

/**
 * Ubiquity\orm\core\prepared$DAOPreparedQueryOne
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
class DAOPreparedQueryOne extends DAOPreparedQueryById {

	public function __construct($className, $condition = '', $included = false) {
		DAOPreparedQuery::__construct ( $className, $condition, $included );
	}

	protected function prepare() {
		$this->conditionParser->limitOne ();
		DAOPreparedQuery::prepare ();
	}
}

