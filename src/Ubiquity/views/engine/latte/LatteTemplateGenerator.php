<?php

namespace Ubiquity\views\engine\latte;

use Ubiquity\utils\base\UArray;
use Ubiquity\utils\base\UString;

class LatteTemplateGenerator extends \Ubiquity\views\engine\TemplateGenerator {

	public function __construct() {
		$this->openExpressionTag = $this->openVarTag = '{';
		$this->closeExpressionTag = $this->closeVarTag = '}';
		$this->safeFilter = 'noescape';
	}

	public function openBlock(string $name): string {
		return $this->openExpressionTag . "block $name" . $this->closeExpressionTag;
	}

	public function closeBlock(): string {
		return $this->openExpressionTag . '/block' . $this->closeExpressionTag;
	}

	public function asArray(array $array): string {
		return UArray::asPhpArray_($array);
	}

	public function insertVariable(string $name, bool $safe = false): string {
		$filter = '';
		if ($safe) {
			$filter = '|' . $this->safeFilter;
		}
		$name = \str_replace('.', '->', $name);
		if (UString::contains('(', $name)) {
			return $this->openVarTag . $name . $filter . $this->closeVarTag;
		}
		return $this->openVarTag . '$' . $name . $filter . $this->closeVarTag;
	}

	public function includeFile(string $filename, bool $asVariable = false): string {
		$quote = "'";
		if ($asVariable) {
			$quote = '';
			$filename = '$' . $filename;
		}
		return $this->openExpressionTag . "include {$quote}{$filename}{$quote}" . $this->closeExpressionTag;
	}

	public function extendsTemplate(string $templateName, bool $asVariable = false): string {
		$quote = "'";
		if ($asVariable) {
			$quote = '';
			$templateName = '$' . $templateName;
		}
		return $this->openExpressionTag . "layout {$quote}{$templateName}{$quote}" . $this->closeExpressionTag;
	}

	public function foreach(string $arrayName, string $value, ?string $key = null): string {
		$arrayName = '$' . $arrayName;
		$value = '$' . $value;
		if ($key != null) {
			$key = '$' . $key;
			return $this->openExpressionTag . "for $arrayName as $key=>$value" . $this->closeExpressionTag;
		}
		return $this->openExpressionTag . "for $arrayName as $value" . $this->closeExpressionTag;
	}

	public function endForeach(): string {
		return $this->openExpressionTag . '/foreach' . $this->closeExpressionTag;
	}

	public function condition(string $condition): string {
		$condition = '$' . \trim($condition);
		return $this->openExpressionTag . "if $condition" . $this->closeExpressionTag;
	}

	public function endCondition(): string {
		return $this->openExpressionTag . '/if' . $this->closeExpressionTag;
	}

	public function getNonce(): string {
		return $this->openExpressionTag . "\$nonce??''" . $this->closeExpressionTag;
	}

	public function getNonceArray(): string {
		return "['nonce'=>\$nonce??'']";
	}

	public function getSelf(): string {
		return $this->openExpressionTag . '$this->getName()' . $this->closeExpressionTag;
	}
}