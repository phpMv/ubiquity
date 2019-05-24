<?php
namespace Ubiquity\controllers\admin\popo;

class TranslateMessage {

	private $mkey;

	private $mvalue;

	private $compare;

	public function __construct($key = '', $value = '', $compare = null) {
		$this->mkey = $key;
		$this->mvalue = $value;
		$this->compare = $compare;
	}

	/**
	 *
	 * @return string
	 */
	public function getMkey() {
		return $this->mkey;
	}

	/**
	 *
	 * @return string
	 */
	public function getMvalue() {
		return $this->mvalue;
	}

	/**
	 *
	 * @param string $key
	 */
	public function setMkey($key) {
		$this->mkey = $key;
	}

	/**
	 *
	 * @param string $value
	 */
	public function setMvalue($value) {
		$this->mvalue = $value;
	}

	/**
	 *
	 * @return string
	 */
	public function getCompare() {
		return $this->compare;
	}

	/**
	 *
	 * @param string $compare
	 */
	public function setCompare($compare) {
		$this->compare = $compare;
	}

	/**
	 *
	 * @param array $messages
	 */
	public static function load($messages) {
		$result = [];
		foreach ($messages as $key => $value) {
			$result[$key] = new TranslateMessage($key, $value);
		}
		return $result;
	}

	/**
	 *
	 * @param array $messages
	 * @param array $compareTo
	 * @param boolean $addInexisting
	 */
	public static function loadAndCompare($messages, $compareTo, $addInexisting = true) {
		$result = [];
		foreach ($messages as $key => $value) {
			$result[$key] = new TranslateMessage($key, $value);
			if (isset($compareTo[$key])) {
				$result[$key]->setCompare($compareTo[$key]);
				unset($compareTo[$key]);
			}
		}
		if ($addInexisting) {
			foreach ($compareTo as $key => $value) {
				$result[$key] = new TranslateMessage($key, '', $value);
			}
		}
		return $result;
	}
}

