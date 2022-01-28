<?php

namespace Ubiquity\utils\base;

/**
 * Ubiquity\utils\base$CodeUtils
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
class CodeUtils {

	public static function cleanParameters($parameters) {
		$optional = false;
		$tmpResult = [ ];
		$params = \explode ( ",", $parameters );
		foreach ( $params as $param ) {
			$param = \trim ( $param );
			$list = \explode ( "=", $param );
			if (isset ( $list [0] )) {
				$var = $list [0];
			}
			if (isset ( $list [1] )) {
				$value = $list [1];
			}
			if (isset ( $var ) && isset ( $value )) {
				$value = \trim ( $value );
				$var = self::checkVar ( $var );
				$tmpResult [] = $var . '=' . $value;
				$optional = true;
			} elseif (isset ( $var )) {
				$var = self::checkVar ( $var );
				if ($optional)
					$tmpResult [] = $var . "=''";
				else
					$tmpResult [] = $var;
			}
		}
		return \implode ( ',', $tmpResult );
	}

	public static function getParametersForRoute($parameters) {
		$tmpResult = [ ];
		$params = \explode ( ",", $parameters );
		foreach ( $params as $param ) {
			$param = \trim ( $param );
			$list = \explode ( "=", $param );
			if (isset ( $list [0] )) {
				$var = $list [0];
			}
			if (isset ( $list [1] )) {
				$value = $list [1];
			}
			if (isset ( $var ) && isset ( $value )) {
				break;
			} elseif (isset ( $var )) {
				$var = self::unCheckVar ( $var );
				$tmpResult [] = '{' . $var . '}';
			}
		}
		return $tmpResult;
	}

	public static function checkVar($var, $prefix = '$') {
		if (UString::isNull ( $var ))
			return "";
		$var = \trim ( $var );
		if (! UString::startswith ( $var, $prefix )) {
			$var = $prefix . $var;
		}
		return $var;
	}

	public static function unCheckVar($var, $prefix = '$') {
		if (UString::isNull ( $var ))
			return '';
		$var = \trim ( $var );
		if (UString::startswith ( $var, $prefix )) {
			$var = \substr ( $var, \count ( $prefix ) );
		}
		return $var;
	}

	public static function indent($code, $count = 2) {
		$tab = \str_repeat ( "\t", $count );
		$lines = \explode ( "\n", $code );
		return $tab . \implode ( $tab, $lines );
	}

	public static function isValidCode($code) {
		$output = [ ];
		$result = 1;
		$temp_file = tempnam ( sys_get_temp_dir (), 'Tux' );
		$fp = \fopen ( $temp_file, 'w' );
		\fwrite ( $fp, $code );
		\fclose ( $fp );
		if (\file_exists ( $temp_file )) {
			$phpExe = self::getPHPExecutable ();
			if (isset ( $phpExe )) {
				exec ( $phpExe . ' -l ' . $temp_file, $output, $result );
			}
			$output = \implode ( '', $output );
			\unlink ( $temp_file );
			if (strpos ( $output, 'No syntax errors detected' ) === false && $result !== 1) {
				return false;
			}
		}
		return true;
	}

	/**
	 *
	 * @see https://stackoverflow.com/questions/2372624/get-current-php-executable-from-within-script
	 * @return string
	 */
	public static function getPHPExecutable() {
		if (defined ( 'PHP_BINARY' ) && PHP_BINARY && in_array ( PHP_SAPI, array ('cli','cli-server' ) ) && is_file ( PHP_BINARY )) {
			return PHP_BINARY;
		} else if (\strtoupper ( substr ( PHP_OS, 0, 3 ) ) === 'WIN') {
			$paths = \explode ( PATH_SEPARATOR, getenv ( 'PATH' ) );
			foreach ( $paths as $path ) {
				if (substr ( $path, strlen ( $path ) - 1 ) == \DS) {
					$path = \substr ( $path, 0, strlen ( $path ) - 1 );
				}
				if (\substr ( $path, strlen ( $path ) - strlen ( 'php' ) ) == 'php') {
					$response = $path . \DS . 'php.exe';
					if (is_file ( $response )) {
						return $response;
					}
				} else if (\substr ( $path, \strlen ( $path ) - \strlen ( 'php.exe' ) ) == 'php.exe') {
					if (\is_file ( $path )) {
						return $path;
					}
				}
			}
		} else {
			$paths = \explode ( PATH_SEPARATOR, getenv ( 'PATH' ) );
			foreach ( $paths as $path ) {
				if (\substr ( $path, \strlen ( $path ) - 1 ) == \DS) {
					$path = substr ( $path, strlen ( $path ) - 1 );
				}
				if (\substr ( $path, \strlen ( $path ) - \strlen ( 'php' ) ) == 'php') {
					if (\is_file ( $path )) {
						return $path;
					}
					$response = $path . \DS . 'php';
					if (\is_file ( $response )) {
						return $response;
					}
				}
			}
		}
		return null;
	}
}
