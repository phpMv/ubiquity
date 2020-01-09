<?php

namespace Ubiquity\cache\preloading;

/**
 * Ubiquity\cache\preloading$PreloaderInternalTrait
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
trait PreloaderInternalTrait {
	private $vendorDir;
	private static $libraries = [
								'application' => './../app/',
								'ubiquity' => 'phpmv/ubiquity/src/Ubiquity/',
								'ubiquity-dev' => 'phpmv/ubiquity-dev/src/Ubiquity/',
								'ubiquity-webtools' => 'phpmv/ubiquity-webtools/src/Ubiquity/',
								'ubiquity-mailer' => 'phpmv/ubiquity-mailer/src/Ubiquity/',
								'ubiquity-swoole' => 'phpmv/ubiquity-swoole/src/Ubiquity/',
								'ubiquity-workerman' => 'phpmv/ubiquity-workerman/src/Ubiquity/',
								'ubiquity-tarantool' => 'phpmv/ubiquity-tarantool/src/Ubiquity/',
								'ubiquity-mysqli' => 'phpmv/ubiquity-mysqli/src/Ubiquity/',
								'phpmv-ui' => 'phpmv/php-mv-ui/Ajax/' ];
	private $excludeds = [ ];
	private static $count = 0;
	private $classes = [ ];
	private $loader;

	private function addClassFile($class, $file) {
		if (! isset ( $this->classes [$class] )) {
			$this->classes [$class] = $file;
		}
	}

	private function loadClass($class, $file = null) {
		if (! \class_exists ( $class, false )) {
			$file = $file ?? $this->getPathFromClass ( $class );
			if (isset ( $file )) {
				$this->loadFile ( $file );
			}
		}
		if (\class_exists ( $class, false )) {
			echo "$class loaded !\n";
		}
	}

	private function getPathFromClass(string $class): ?string {
		$classPath = $this->loader->findFile ( $class );
		if (false !== $classPath) {
			return \realpath ( $classPath );
		}
		return null;
	}

	private function loadFile(string $file): void {
		require_once ($file);
		self::$count ++;
	}

	private function isExcluded(string $name): bool {
		foreach ( $this->excludeds as $excluded ) {
			if (\strpos ( $name, $excluded ) === 0) {
				return true;
			}
		}
		return false;
	}

	private function glob_recursive($pattern, $flags = 0) {
		$files = \glob ( $pattern, $flags );
		foreach ( \glob ( \dirname ( $pattern ) . '/*', GLOB_ONLYDIR | GLOB_NOSORT ) as $dir ) {
			$files = \array_merge ( $files, $this->glob_recursive ( $dir . '/' . \basename ( $pattern ), $flags ) );
		}
		return $files;
	}

	private function getClassFullNameFromFile($filePathName, $backSlash = false) {
		$phpCode = \file_get_contents ( $filePathName );
		$class = $this->getClassNameFromPhpCode ( $phpCode );
		if (isset ( $class )) {
			$ns = $this->getClassNamespaceFromPhpCode ( $phpCode );
			if ($backSlash && $ns != null) {
				$ns = "\\" . $ns;
			}
			return $ns . '\\' . $class;
		}
		return null;
	}

	private function getClassNamespaceFromPhpCode($phpCode) {
		$tokens = \token_get_all ( $phpCode );
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
		}
		return $namespace;
	}

	private function getClassNameFromPhpCode($phpCode) {
		$classes = array ();
		$tokens = \token_get_all ( $phpCode );
		$count = count ( $tokens );
		for($i = 2; $i < $count; $i ++) {
			$elm = $tokens [$i - 2] [0];
			if ($elm == T_CLASS && $tokens [$i - 1] [0] == T_WHITESPACE && $tokens [$i] [0] == T_STRING) {
				$class_name = $tokens [$i] [1];
				$classes [] = $class_name;
			}
		}
		if (isset ( $classes [0] ))
			return $classes [0];
		return null;
	}

	private function asPhpArray($array, $prefix = "", $depth = 1, $format = false) {
		$exts = array ();
		$extsStr = "";
		$tab = "";
		$nl = "";
		if ($format) {
			$tab = \str_repeat ( "\t", $depth );
			$nl = PHP_EOL;
		}
		foreach ( $array as $k => $v ) {
			if (\is_string ( $k )) {
				$exts [] = "\"" . $this->doubleBackSlashes ( $k ) . "\"=>" . $this->parseValue ( $v, 'array', $depth + 1, $format );
			} else {
				$exts [] = $this->parseValue ( $v, $prefix, $depth + 1, $format );
			}
		}
		if (\sizeof ( $exts ) > 0 || $prefix !== "") {
			$extsStr = "(" . \implode ( "," . $nl . $tab, $exts ) . ")";
			if (\sizeof ( $exts ) > 0) {
				$extsStr = "(" . $nl . $tab . \implode ( "," . $nl . $tab, $exts ) . $nl . $tab . ")";
			}
		}
		return $prefix . $extsStr;
	}

	private function parseValue($v, $prefix = "", $depth = 1, $format = false) {
		if (\is_array ( $v )) {
			$result = $this->asPhpArray ( $v, $prefix, $depth + 1, $format );
		} elseif ($v instanceof \Closure) {
			$result = $this->closure_dump ( $v );
		} else {
			$result = $this->doubleBackSlashes ( $v );
			$result = "\"" . \str_replace ( [ '$','"' ], [ '\$','\"' ], $result ) . "\"";
		}
		return $result;
	}

	private function closure_dump(\Closure $c) {
		$str = 'function (';
		$r = new \ReflectionFunction ( $c );
		$params = array ();
		foreach ( $r->getParameters () as $p ) {
			$s = '';
			if ($p->isArray ()) {
				$s .= 'array ';
			} else if ($p->getClass ()) {
				$s .= $p->getClass ()->name . ' ';
			}
			if ($p->isPassedByReference ()) {
				$s .= '&';
			}
			$s .= '$' . $p->name;
			if ($p->isOptional ()) {
				$s .= ' = ' . \var_export ( $p->getDefaultValue (), TRUE );
			}
			$params [] = $s;
		}
		$str .= \implode ( ', ', $params );
		$str .= ')';
		$lines = file ( $r->getFileName () );
		$sLine = $r->getStartLine ();
		$eLine = $r->getEndLine ();
		if ($eLine === $sLine) {
			$match = \strstr ( $lines [$sLine - 1], "function" );
			$str .= \strstr ( \strstr ( $match, "{" ), "}", true ) . "}";
		} else {
			$str .= \strrchr ( $lines [$sLine - 1], "{" );
			for($l = $sLine; $l < $eLine - 1; $l ++) {
				$str .= $lines [$l];
			}
			$str .= \strstr ( $lines [$eLine - 1], "}", true ) . "}";
		}
		$vars = $r->getStaticVariables ();
		foreach ( $vars as $k => $v ) {
			$str = \str_replace ( '$' . $k, \var_export ( $v, true ), $str );
		}
		return $str;
	}

	private function doubleBackSlashes($value) {
		if (\is_string ( $value ))
			return \str_replace ( "\\", "\\\\", $value );
		return $value;
	}
}

