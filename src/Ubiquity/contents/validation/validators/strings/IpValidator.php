<?php

namespace Ubiquity\contents\validation\validators\strings;


use Ubiquity\contents\validation\validators\ValidatorHasNotNull;

/**
 * Validates an address ip
 * Usage @validator("ip","4")
 * Inspired from Bernhard Schussek Symfony IpValidator
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 */
class IpValidator extends ValidatorHasNotNull {
	protected $ref=Ip::V4;
	
	public function __construct(){
		$this->message="{value} is not a valid ip of type {ref}";
	}
	
	public function validate($value) {
		parent::validate($value);
		$value = (string) $value;
		
		switch ($this->ref) {
			case Ip::V4:
				$flag = FILTER_FLAG_IPV4;
				break;
			case Ip::V6:
				$flag = FILTER_FLAG_IPV6;
				break;
			case Ip::V4_NO_PRIV:
				$flag = FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE;
				break;
			case Ip::V6_NO_PRIV:
				$flag = FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE;
				break;
			case Ip::ALL_NO_PRIV:
				$flag = FILTER_FLAG_NO_PRIV_RANGE;
				break;
			case Ip::V4_NO_RES:
				$flag = FILTER_FLAG_IPV4 | FILTER_FLAG_NO_RES_RANGE;
				break;
			case Ip::V6_NO_RES:
				$flag = FILTER_FLAG_IPV6 | FILTER_FLAG_NO_RES_RANGE;
				break;
			case Ip::ALL_NO_RES:
				$flag = FILTER_FLAG_NO_RES_RANGE;
				break;
			case Ip::V4_ONLY_PUBLIC:
				$flag = FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;
				break;
			case Ip::V6_ONLY_PUBLIC:
				$flag = FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;
				break;
			case Ip::ALL_ONLY_PUBLIC:
				$flag = FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;
				break;
			default:
				$flag = null;
				break;
		}
		
		return filter_var($value, FILTER_VALIDATE_IP, $flag);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Ubiquity\contents\validation\validators\Validator::getParameters()
	 */
	public function getParameters(): array {
		return ["value","ref"];
	}
}

