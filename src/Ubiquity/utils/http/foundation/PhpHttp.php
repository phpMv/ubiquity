<?php

namespace Ubiquity\utils\http\foundation;

/**
 * Ubiquity\utils\http\foundation$PhpHttp
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.1
 *
 */
class PhpHttp extends AbstractHttp {

	public function getAllHeaders() {
		return \getallheaders ();
	}

	public function header($key, $value, bool $replace = true, int $http_response_code = null) {
		\header ( $key . ': ' . $value, $replace, $http_response_code );
	}

	public function headersSent(string &$file = null, int &$line = null) {
		return \headers_sent ( $file, $line );
	}

	public function getInput() {
		$put = array ();
		\parse_str ( \file_get_contents ( 'php://input' ), $put );
		return $put;
	}
}

