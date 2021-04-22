<?php

namespace Ubiquity\cache;

use Ubiquity\utils\base\UString;

/**
 * Manipulates class and namespace names
 * Ubiquity\cache$ClassUtils
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.1
 *
 */
class ClassUtils {

	private static function getClassNamespaceFromPhpCode($phpCode) {
		$tokens = token_get_all ( $phpCode );
		$count = \count ( $tokens );
		$i = 0;
		$namespace = '';
		$namespace_ok = false;
		while ( $i < $count ) {
			$token = $tokens [$i];
			if (\is_array ( $token ) && $token [0] === T_NAMESPACE) {
				// Found namespace declaration
				while ( ++ $i < $count ) {
					if ($tokens [$i] === ';') {
						$namespace_ok = true;
						$namespace = \trim ( $namespace );
						break;
					}
					$namespace .= \is_array ( $tokens [$i] ) ? $tokens [$i] [1] : $tokens [$i];
				}
				break;
			}
			$i ++;
		}
		if (! $namespace_ok) {
			return null;
		} else {
			return $namespace;
		}
	}

	private static function getClassNameFromPhpCode($phpCode) {
		$classes = array ();
		$tokens = \token_get_all ( $phpCode );
		$count = count ( $tokens );
		for($i = 2; $i < $count; $i ++) {
			if (($tokens [$i - 2] [0] == T_TRAIT || $tokens [$i - 2] [0] == T_CLASS )&& $tokens [$i - 1] [0] == T_WHITESPACE && $tokens [$i] [0] == T_STRING) {
				$class_name = $tokens [$i] [1];
				$classes [] = $class_name;
			}
		}
		if (isset ( $classes [0] ))
			return $classes [0];
		return null;
	}

	/**
	 * get the full name (name \ namespace) of a class from its file path
	 * result example: (string) "I\Am\The\Namespace\Of\This\Class"
	 *
	 * @param $filePathName
	 *
	 * @return string
	 */
	public static function getClassFullNameFromFile($filePathName, $backSlash = false) {
		$phpCode = \file_get_contents ( $filePathName );
		$ns = self::getClassNamespaceFromPhpCode ( $phpCode );
		if ($backSlash && UString::isNotNull ( $ns )) {
			$ns = "\\" . $ns;
		}
		$class=self::getClassNameFromPhpCode ( $phpCode );
		if($class!=null) {
			return $ns . '\\' . self::getClassNameFromPhpCode($phpCode);
		}
		return null;
	}

	public static function cleanClassname($classname) {
		return \str_replace ( "\\", "\\\\", $classname );
	}

	/**
	 * Returns a cleanly namespace
	 *
	 * @param array|string $parts
	 * @return string
	 */
	public static function getNamespaceFromParts($parts) {
		$resultArray = [ ];
		if (! \is_array ( $parts )) {
			$parts = [ $parts ];
		}
		foreach ( $parts as $part ) {
			$resultArray = \array_merge ( $resultArray, \explode ( "\\", $part ) );
		}
		$resultArray = \array_diff ( $resultArray, [ "" ] );
		return \implode ( "\\", $resultArray );
	}

	/**
	 * Returns the namespace from a complete classname
	 *
	 * @param string $completeClassname
	 * @return string
	 */
	public static function getNamespaceFromCompleteClassname($completeClassname) {
		$position = \strrpos ( $completeClassname, '\\' );
		return \substr ( $completeClassname, 0, $position );
	}

	/**
	 * build and return an object of a class from its file path
	 *
	 * @param $filePathName
	 *
	 * @return mixed
	 */
	public static function getClassObjectFromFile($filePathName) {
		$classString = self::getClassFullNameFromFile ( $filePathName );
		$object = new $classString ();
		return $object;
	}

	/**
	 * get the class namespace form file path using token
	 *
	 * @param $filePathName
	 *
	 * @return null|string
	 */
	public static function getClassNamespaceFromFile($filePathName) {
		$phpCode = \file_get_contents ( $filePathName );
		return self::getClassNamespaceFromPhpCode ( $phpCode );
	}

	/**
	 * get the class name from file path using token
	 *
	 * @param $filePathName
	 *
	 * @return mixed
	 */
	public static function getClassNameFromFile($filePathName) {
		$phpCode = \file_get_contents ( $filePathName );
		return self::getClassNameFromPhpCode ( $phpCode );
	}

	/**
	 * Returns the complete name of a class
	 *
	 * @param string $defaultNS
	 * @param string $name
	 * @return string
	 */
	public static function getClassNameWithNS($defaultNS, $name) {
		if (\strpos ( $name, "\\" ) === false) {
			$name = $defaultNS . "\\" . $name;
		}
		return $name;
	}

	/**
	 * Returns the simple class name of a class, without namespace
	 *
	 * @param string $classnameWithNamespace
	 * @return string
	 */
	public static function getClassSimpleName($classnameWithNamespace) {
		if (($pos = \strrpos ( $classnameWithNamespace, '\\' )) !== false) {
			return \substr ( $classnameWithNamespace, $pos + 1 );
		}
		return $classnameWithNamespace;
	}
}
