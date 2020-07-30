<?php
namespace Ubiquity\orm\core\prepared;

use Ubiquity\cache\database\DbCache;

/**
 * Ubiquity\orm\core\prepared$DAOPreparedQueryOne
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.2
 *
 */
class DAOPreparedQueryOne extends DAOPreparedQueryById {

	public function __construct($className, $condition = '', $included = false, $cache = null) {
		DAOPreparedQuery::__construct($className, $condition, $included, $cache);
	}

	protected function prepare(?DbCache $cache = null) {
		$this->conditionParser->limitOne();
		DAOPreparedQuery::prepare($cache);
		$this->updatePrepareStatement();
	}
}

