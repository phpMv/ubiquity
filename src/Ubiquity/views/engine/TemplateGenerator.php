<?php

namespace Ubiquity\views\engine;

use Ubiquity\utils\base\UString;

/**
 * Ubiquity abstract TemplateGenerator class.
 *
 * Ubiquity\views\engine$TemplateGenerator
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
abstract class TemplateGenerator {

	protected string $openVarTag;
	protected string $closeVarTag;
	protected string $openExpressionTag;
	protected string $closeExpressionTag;
	protected string $safeFilter;

	abstract public function openBlock(string $name): string;

	abstract public function closeBlock(): string;

	abstract public function asArray(array $array): string;

	abstract public function insertVariable(string $name, bool $safe = false): string;

	abstract public function includeFile(string $filename, bool $asVariable = false): string;

	abstract public function extendsTemplate(string $templateName, bool $asVariable = false): string;

	abstract public function foreach(string $arrayName, string $value, ?string $key = null): string;

	abstract public function endForeach(): string;

	abstract public function condition(string $condition): string;

	abstract public function endCondition(): string;

	abstract public function getNonce(): string;

	abstract public function getNonceArray(): string;

	abstract public function getSelf(): string;

	abstract public function asVariable(string $var): string;

	/**
	 * @return string
	 */
	public function getOpenVarTag(): string {
		return $this->openVarTag;
	}

	/**
	 * @return string
	 */
	public function getCloseVarTag(): string {
		return $this->closeVarTag;
	}

	/**
	 * @return string
	 */
	public function getOpenExpressionTag(): string {
		return $this->openExpressionTag;
	}

	/**
	 * @return string
	 */
	public function getCloseExpressionTag(): string {
		return $this->closeExpressionTag;
	}

	private function parseValue($v) {
		if (\is_numeric($v) && gettype($v) !== 'string') {
			$result = $v;
		} elseif ($v !== '' && UString::isBooleanStr($v)) {
			$result = UString::getBooleanStr($v);
		} elseif (\is_array($v)) {
			$result = $this->asArray($v);
		} elseif (\is_string($v) && UString::isExpression($v)) {
			$result = \trim($v);
		} else {
			$result = "\"" . \str_replace(['$', '"'], ['\$', '\"'], $v) . "\"";
		}
		return $result;
	}

	protected function implodeParameters(array $parameters): string {
		$result = [];
		foreach ($parameters as $param) {
			$result[] = $this->parseValue($param);
		}
		return \implode(',', $result);
	}

	protected function insertAssets(string $funcName, string $url) {
		return $this->openExpressionTag . "$funcName('" . $url . "'," . $this->getNonceArray() . ")" . $this->closeExpressionTag;
	}

	public function insertJS(string $url): string {
		return $this->insertAssets('js', $url);
	}

	public function insertCSS(string $url): string {
		return $this->insertAssets('css', $url);
	}

	public function callFunction(string $funcName, ...$parameters): string {
		return $this->openExpressionTag . "$funcName(" . $this->implodeParameters($parameters) . ')' . $this->closeExpressionTag;
	}

}
