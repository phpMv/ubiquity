<?php

namespace Ubiquity\contents\validation\validators\strings;

use Ubiquity\contents\validation\validators\ValidatorHasNotNull;

/**
 * Validates an address ip
 * Usage @validator("ip","4")
 * Inspired from Bernhard Schussek Symfony IpValidator
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.1
 */
class IpValidator extends ValidatorHasNotNull {
	protected $ref = Ip::V4;
	const FLAGS = [
					Ip::V4 => FILTER_FLAG_IPV4,
					Ip::V6 => FILTER_FLAG_IPV6,
					Ip::V4_NO_PRIV => FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE,
					Ip::V6_NO_PRIV => FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE,
					Ip::ALL_NO_PRIV => FILTER_FLAG_NO_PRIV_RANGE,
					Ip::V4_NO_RES => FILTER_FLAG_IPV4 | FILTER_FLAG_NO_RES_RANGE,
					Ip::V6_NO_RES => FILTER_FLAG_IPV6 | FILTER_FLAG_NO_RES_RANGE,
					Ip::ALL_NO_RES => FILTER_FLAG_NO_RES_RANGE,
					Ip::V4_ONLY_PUBLIC => FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE,
					Ip::V6_ONLY_PUBLIC => FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE,
					Ip::ALL_ONLY_PUBLIC => FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ];

	public function __construct() {
		$this->message = "{value} is not a valid ip of type {ref}";
	}

	public function validate($value) {
		parent::validate ( $value );
		$value = ( string ) $value;
		$flag = null;
		if ($this->notNull !== false) {
			if (isset ( self::FLAGS [$this->ref] )) {
				$flag = self::FLAGS [$this->ref];
			}
			return filter_var ( $value, FILTER_VALIDATE_IP, $flag );
		}
		return true;
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\contents\validation\validators\Validator::getParameters()
	 */
	public function getParameters(): array {
		return [ "value","ref" ];
	}
}

