<?php

/**
 * Validation managment
 */
namespace Ubiquity\contents\validation;

use Ubiquity\annotations\ValidatorAnnotation;
use Ubiquity\db\utils\DbTypes;
use Ubiquity\contents\validation\validators\HasNotNullInterface;

class ValidationModelGenerator {
	protected $type;
	protected $name;
	protected $notNull;
	protected $primary;

	public function __construct($type, $name, $notNull, $primary) {
		$this->type = $type;
		$this->name = $name;
		$this->notNull = $notNull;
		$this->primary = $primary;
	}

	protected function parseType($type, $size) {
		switch ($type) {
			case "tinyint" :
				if ($size == 1) {
					return ValidatorAnnotation::initializeFromModel ( "isBool" );
				}
				break;
			case "date" :
				return ValidatorAnnotation::initializeFromModel ( "type", "date" );
			case "datetime" :
				return ValidatorAnnotation::initializeFromModel ( "type", "dateTime" );
			case "time" :
				return ValidatorAnnotation::initializeFromModel ( "type", "time" );
		}
		return null;
	}

	protected function parseSize($type, $size) {
		if (isset ( $size )) {
			if (DbTypes::isString ( $type )) {
				return ValidatorAnnotation::initializeFromModel ( "length", null, [ "max" => $size ] );
			}
		}
		return null;
	}

	protected function parseNotNull(&$validatorAnnots) {
		if ($this->notNull) {
			$notNullAffected = false;
			$size = sizeof ( $validatorAnnots );
			$i = 0;
			while ( $i < $size && ! $notNullAffected ) {
				$validatorAnnot = $validatorAnnots [$i];
				$validatorClass = ValidatorsManager::$validatorTypes [$validatorAnnot->type];
				if (is_subclass_of ( $validatorClass, HasNotNullInterface::class, true )) {
					$validatorAnnots [$i]->constraints ["notNull"] = true;
					$notNullAffected = true;
				}
				$i ++;
			}
			if (! $notNullAffected) {
				$validatorAnnots [] = ValidatorAnnotation::initializeFromModel ( "notNull" );
			}
		}
	}

	protected function parseName() {
		switch ($this->name) {
			case "email" :
			case "mail" :
				return ValidatorAnnotation::initializeFromModel ( "email" );
			case "url" :
				return ValidatorAnnotation::initializeFromModel ( "url" );
		}
		return null;
	}

	protected function scanType(&$type, &$size) {
		$type = DbTypes::getType ( $this->type );
		$size = DbTypes::getSize ( $this->type );
	}

	public function parse() {
		if ($this->primary && DbTypes::isInt ( $this->type )) {
			return [ ValidatorAnnotation::initializeFromModel ( "id", null, [ "autoinc" => true ] ) ];
		}
		$validatorAnnot = $this->parseName ();
		$this->scanType ( $type, $size );
		if (! isset ( $validatorAnnot )) {
			$validatorAnnot = $this->parseType ( $type, $size );
		}

		$result = [ ];
		if (isset ( $validatorAnnot )) {
			$result [] = $validatorAnnot;
		}
		$validatorAnnot = $this->parseSize ( $type, $size );
		if (isset ( $validatorAnnot )) {
			$result [] = $validatorAnnot;
		}
		$this->parseNotNull ( $result );
		return $result;
	}
}

