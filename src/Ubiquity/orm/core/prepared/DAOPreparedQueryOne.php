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

	protected function prepare() {
		$this->conditionParser->setCondition ( $this->condition );
		$this->conditionParser->limitOne ();
		DAOPreparedQuery::prepare ();
	}
}

