<?php

namespace Ubiquity\utils\http\session\protection;

/**
 * Ubiquity\utils\http\session\protection$CsrfToken
 * This class is part of Ubiquity
 *
 * @author jc
 * @version 1.0.0
 *
 */
class CsrfToken {
	private $name;
	private $value;

	public function __construct($size = 32) {
		$this->name = $this->generate ( $size );
		$this->value = $this->generate ( $size );
	}

	private function generate($size): string {
		$bytes = \random_bytes ( $size );
		return \rtrim ( \strtr ( \base64_encode ( $bytes ), '+/', '-_' ), '=' );
	}

	/**
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 *
	 * @return string
	 */
	public function getValue() {
		return $this->value;
	}
}

