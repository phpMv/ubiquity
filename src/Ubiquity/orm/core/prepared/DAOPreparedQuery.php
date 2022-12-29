<?php

namespace Ubiquity\orm\core\prepared;

use Ubiquity\cache\database\DbCache;
use Ubiquity\db\SqlUtils;
use Ubiquity\orm\DAO;
use Ubiquity\orm\OrmUtils;
use Ubiquity\orm\parser\ConditionParser;

/**
 * Ubiquity\orm\core\prepared$DAOPreparedQuery
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.7
 *
 */
abstract class DAOPreparedQuery {
	protected $databaseOffset;

	/**
	 *
	 * @var ConditionParser
	 */
	protected $conditionParser;
	protected $included;
	protected $hasIncluded;
	protected $useCache;
	protected $className;
	protected $tableName;
	protected $invertedJoinColumns = null;
	protected $oneToManyFields = null;
	protected $manyToManyFields = null;
	protected $transformers;
	protected $propsKeys;
	protected $accessors;
	protected $fieldList;
	protected $memberList;
	protected $firstPropKey;
	protected $condition;
	protected $preparedCondition;
	protected $primaryKeys;

	/**
	 *
	 * @var array|boolean
	 */
	protected $additionalMembers = false;
	protected $sqlAdditionalMembers = "";
	protected $allPublic = false;
	protected $statement;

	/**
	 *
	 * @var \Ubiquity\db\Database
	 */
	protected $db;

	public function __construct($className, $condition = null, $included = false, $cache = null) {
		$this->className = $className;
		$this->included = $included;
		$this->condition = $condition;
		$this->conditionParser = new ConditionParser ($condition);
		$this->prepare($cache);
	}

	public function getFirstPropKey() {
		return $this->firstPropKey;
	}

	/**
	 *
	 * @return \Ubiquity\db\Database
	 */
	public function getDb() {
		return $this->db;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getDatabaseOffset() {
		return $this->databaseOffset;
	}

	/**
	 *
	 * @return \Ubiquity\orm\parser\ConditionParser
	 */
	public function getConditionParser() {
		return $this->conditionParser;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getIncluded() {
		return $this->included;
	}

	/**
	 *
	 * @return boolean
	 */
	public function getHasIncluded() {
		return $this->hasIncluded;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getUseCache() {
		return $this->useCache;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getClassName() {
		return $this->className;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getTableName() {
		return $this->tableName;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getInvertedJoinColumns() {
		return $this->invertedJoinColumns;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getOneToManyFields() {
		return $this->oneToManyFields;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getManyToManyFields() {
		return $this->manyToManyFields;
	}

	public function getTransformers() {
		return $this->transformers;
	}

	public function getPropsKeys() {
		return $this->propsKeys;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getAccessors() {
		return $this->accessors;
	}

	public function getFieldList() {
		return $this->fieldList;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getMemberList() {
		return $this->memberList;
	}

	protected function prepare(?DbCache $cache = null) {
		$this->db = DAO::getDb($this->className);
		if (isset ($cache)) {
			$this->db->setCacheInstance($cache);
		}
		$this->included = DAO::_getIncludedForStep($this->included);

		$metaDatas = OrmUtils::getModelMetadata($this->className);
		$this->tableName = $metaDatas ['#tableName'];
		$this->hasIncluded = $this->included || (\is_array($this->included) && \count($this->included) > 0);
		if ($this->hasIncluded) {
			DAO::_initRelationFields($this->included, $metaDatas, $this->invertedJoinColumns, $this->oneToManyFields, $this->manyToManyFields);
		}
		$this->transformers = $metaDatas ['#transformers'] [DAO::$transformerOp] ?? [];
		$this->fieldList = DAO::_getFieldList($this->tableName, $metaDatas);
		$this->memberList = \array_flip(\array_diff($metaDatas ['#fieldNames'], $metaDatas ['#notSerializable']));
		$this->propsKeys = OrmUtils::getPropKeys($this->className);

		$this->firstPropKey = OrmUtils::getFirstPropKey($this->className);
		$this->primaryKeys = OrmUtils::getPrimaryKeys($this->className);
		if (!($this->allPublic = OrmUtils::hasAllMembersPublic($this->className))) {
			$this->accessors = $metaDatas ['#accessors'];
		}
	}

	protected function updatePrepareStatement() {
		$this->preparedCondition = SqlUtils::checkWhere($this->conditionParser->getCondition());
		$this->statement = $this->db->getDaoPreparedStatement($this->tableName, $this->preparedCondition, $this->fieldList . $this->sqlAdditionalMembers);
	}

	protected function updateSqlAdditionalMembers() {
		if ($this->additionalMembers) {
			$this->sqlAdditionalMembers = ',' . $this->parseExpressions();
			$this->updatePrepareStatement();
		}
	}

	protected function parseExpressions() {
		return \implode(',', $this->additionalMembers);
	}

	protected function addAditionnalMembers($object, $row) {
		foreach ($this->additionalMembers as $member => $_) {
			$object->{$member} = $row [$member] ?? null;
			$object->_rest [$member] = $row [$member] ?? null;
		}
	}

	abstract public function execute($params = [], $useCache = false);

	/**
	 * Adds a new expression and associates it with a new member of the class added at runtime.
	 *
	 * @param string $sqlExpression The SQL expression (part of the SQL SELECT)
	 * @param string $memberName The new associated member name
	 */
	public function addMember(string $sqlExpression, string $memberName): void {
		$this->additionalMembers [$memberName] = $sqlExpression . " AS '{$memberName}'";
		$this->updateSqlAdditionalMembers();
	}

	/**
	 * Adds new expressions and their associated members at runtime.
	 *
	 * @param array $expressionsNames An associative array of [memberName=>sqlExpression,...]
	 */
	public function addMembers(array $expressionsNames): void {
		foreach ($expressionsNames as $member => $expression) {
			$this->additionalMembers [$member] = $expression . " AS '{$member}'";
		}
		$this->updateSqlAdditionalMembers();
	}

	/**
	 * Store the cache for a prepared Query
	 */
	public function storeDbCache() {
		$this->db->storeCache();
	}
}
