<?php

namespace Ubiquity\orm\creator\yuml;

class YumlParser {
	private $stereotypes = [ "pk" => "«pk»","null" => "«null»" ];
	private $defaultType = "varchar(30)";
	private $originalString;
	private $parts;
	private $tables = [ ];

	public function __construct($yumlString) {
		$this->originalString = $yumlString;
		$this->parse ();
	}

	private function getFkName($table, $prefix = "id") {
		return $prefix . \ucfirst ( $table );
	}

	private function parse() {
		$str = $this->originalString;
		$this->parts = \preg_split ( '@\ *?,\ *?@', $str );
		foreach ( $this->parts as $part ) {
			$this->parsePart ( $part );
		}
		$this->parseAllProperties ();
	}

	private function parsePart($part) {
		$matches = [ ];
		\preg_match_all ( '@\[\w+[\|\]]@', $part, $matches );
		if (\sizeof ( $matches [0] ) > 0) {
			foreach ( $matches [0] as $match ) {
				$table = \substr ( $match, 1, \strlen ( $match ) - 2 );
				$this->tables [$table] = [ ];
			}
		}
	}

	private function parseAllProperties() {
		$tables = \array_keys ( $this->tables );
		foreach ( $tables as $table ) {
			$matchProperties = [ ];
			\preg_match ( '@\[' . $table . '\|(.*?)\]@', $this->originalString, $matchProperties );
			if (isset ( $matchProperties [1] )) {
				$properties = $matchProperties [1];
				$this->parseProperties ( $properties, $table );
			}
		}
		foreach ( $tables as $table ) {
			$this->parseRelations ( $table );
		}
		foreach ( $tables as $table ) {
			$this->parseManyRelations ( $table );
		}
	}

	private function parseProperties($propertiesString, $table) {
		$properties = \explode ( ";", $propertiesString );
		foreach ( $properties as $property ) {
			$result = $this->parseProperty ( $property );
			if (! isset ( $this->tables [$table] ["properties"] ))
				$this->tables [$table] ["properties"] = [ ];
			$this->tables [$table] ["properties"] [] = $result;
		}
	}

	private function parseProperty($property) {
		$matches = [ ];
		$result = [ ];
		\preg_match_all ( '@«(.+?)»@', $property, $matches );
		if (is_array ( $matches )) {
			foreach ( $matches as $match ) {
				if (isset ( $match [0] )) {
					$property = \str_replace ( $match [0], "", $property );
					switch ($match [0]) {
						case $this->stereotypes ["pk"] :
							$result ["pk"] = true;
							break;
						case $this->stereotypes ["null"] :
							$result ["null"] = true;
					}
				}
			}
		}
		$parts = \explode ( ":", $property );
		\preg_match ( '@\ *?(\w+)@', $parts [0], $match );
		if (isset ( $match [1] ))
			$result ["name"] = $match [1];
		if (isset ( $parts [1] )) {
			$result ["type"] = $parts [1];
		} else {
			$result ["type"] = $this->defaultType;
		}
		return $result;
	}

	public function getFirstKey($table) {
		$result = null;
		foreach ( $this->tables [$table] ["properties"] as $property ) {
			if (! isset ( $result ))
				$result = $property ["name"];
			if (isset ( $property ["pk"] ) && $property ["pk"])
				return $property ["name"];
		}
		return $result;
	}

	public function getFieldType($table, $fieldName) {
		foreach ( $this->tables [$table] ["properties"] as $property ) {
			if ($property ["name"] === $fieldName)
				return $property ["type"];
		}
		return null;
	}

	public function getPrimaryKeys($table) {
		$result = [ ];
		foreach ( $this->tables [$table] ["properties"] as $property ) {
			if (isset ( $property ["pk"] ) && $property ["pk"])
				$result [] = $property ["name"];
		}
		return $result;
	}

	private function parseRelations($table) {
		$matches = [ ];
		\preg_match_all ( '@\[' . $table . '\][^,]*?1-.*?\[(\w+)\]@', $this->originalString, $matches );
		$this->_parseRelations ( $table, $matches );
		\preg_match_all ( '@\[(\w+)\].*?-[^,]*?1\[' . $table . '\]@', $this->originalString, $matches );
		$this->_parseRelations ( $table, $matches );
	}

	private function _parseRelations($table, $matches) {
		if (\sizeof ( $matches ) > 1) {
			$pk = $this->getFirstKey ( $table );
			if (isset ( $pk )) {
				foreach ( $matches [1] as $match ) {
					$tableName = $match;
					$fk = $this->getFkName ( $table, $pk );
					$this->tables [$table] ["relations"] [] = [ "TABLE_NAME" => $tableName,"COLUMN_NAME" => $fk ];
				}
			}
		}
	}

	private function parseManyRelations($table) {
		$matches = [ ];
		\preg_match_all ( '@\[' . $table . '\][^,]*?\*-.*?\*\[(\w+)\]@', $this->originalString, $matches );
		$this->_parseManyRelations ( $table, $matches );
	}

	private function _parseManyRelations($table, $matches) {
		$myPk = $this->getFirstKey ( $table );
		$myFk = $this->getFkName ( $table, $myPk );
		$myFkType = $this->getFieldType ( $table, $myPk );
		if (\sizeof ( $matches ) > 1) {
			foreach ( $matches [1] as $match ) {
				$tableName = $match;
				$pk = $this->getFirstKey ( $tableName );
				if (isset ( $pk )) {
					$fk = $this->getFkName ( $tableName, $pk );
					$fkType = $this->getFieldType ( $tableName, $pk );
					$newTable = $table . "_" . $tableName;
					$this->tables [$newTable] = [ ];
					$this->tables [$newTable] ["properties"] [] = [ "name" => $myFk,"type" => $myFkType,"pk" => true ];
					$this->tables [$newTable] ["properties"] [] = [ "name" => $fk,"type" => $fkType,"pk" => true ];
					$this->tables [$tableName] ["relations"] [] = [ "TABLE_NAME" => $newTable,"COLUMN_NAME" => $fk ];
					$this->tables [$table] ["relations"] [] = [ "TABLE_NAME" => $newTable,"COLUMN_NAME" => $myFk ];
				}
			}
		}
	}

	public function getParts() {
		return $this->parts;
	}

	public function getTables() {
		return $this->tables;
	}

	public function getTableNames() {
		return \array_keys ( $this->tables );
	}

	public function getFields($table) {
		return $this->tables [$table] ["properties"];
	}

	public function getForeignKeys($table) {
		if (isset ( $this->tables [$table] ["relations"] ))
			return $this->tables [$table] ["relations"];
		return [ ];
	}
}
