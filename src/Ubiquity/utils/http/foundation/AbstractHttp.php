<?php

namespace Ubiquity\utils\http\foundation;

/**
 * Ubiquity\utils\http\foundation$AbstractHttp
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.2
 *
 */
abstract class AbstractHttp {

	public abstract function getAllHeaders();

	public abstract function header($key, $value, bool $replace = true, int $http_response_code = 0);

	public abstract function headersSent(string &$file = null, int &$line = null);

	public abstract function getInput();
}

