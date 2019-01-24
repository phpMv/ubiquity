<?php

namespace Ubiquity\utils\base;

use Ubiquity\utils\base\traits\UFileSystemWriter;

/**
 * File system utilities
 *
 * @author jcheron <myaddressmail@gmail.com>"
 * @version 1.0.2
 */
class UFileSystem {
	use UFileSystemWriter;

	public static function glob_recursive($pattern, $flags = 0) {
		$files = \glob ( $pattern, $flags );
		foreach ( \glob ( \dirname ( $pattern ) . '/*', GLOB_ONLYDIR | GLOB_NOSORT ) as $dir ) {
			$files = \array_merge ( $files, self::glob_recursive ( $dir . '/' . \basename ( $pattern ), $flags ) );
		}
		return $files;
	}

	public static function deleteAllFilesFromFolder($folder) {
		$files = \glob ( $folder . '/*' );
		foreach ( $files as $file ) {
			if (\is_file ( $file ))
				\unlink ( $file );
		}
	}

	public static function deleteFile($filename) {
		if (\file_exists ( $filename ))
			return \unlink ( $filename );
		return false;
	}

	public static function safeMkdir($dir) {
		if (! \is_dir ( $dir ))
			return \mkdir ( $dir, 0777, true );
		return true;
	}

	public static function cleanPathname($path) {
		if (UString::isNotNull ( $path )) {
			if (\DS === "/")
				$path = \str_replace ( "\\", \DS, $path );
			else
				$path = \str_replace ( "/", \DS, $path );
			$path = \str_replace ( \DS . \DS, \DS, $path );
			if (! UString::endswith ( $path, \DS )) {
				$path = $path . \DS;
			}
		}
		return $path;
	}

	public static function cleanFilePathname($path) {
		if (UString::isNotNull ( $path )) {
			if (\DS === "/")
				$path = \str_replace ( "\\", \DS, $path );
			else
				$path = \str_replace ( "/", \DS, $path );
			$path = \str_replace ( \DS . \DS, \DS, $path );
		}
		return $path;
	}

	public static function tryToRequire($file) {
		if (\file_exists ( $file )) {
			require_once ($file);
			return true;
		}
		return false;
	}

	public static function lastModified($filename) {
		return \filemtime ( $filename );
	}

	public static function load($filename) {
		if (\file_exists ( $filename )) {
			return \file_get_contents ( $filename );
		}
		return false;
	}

	public static function getDirFromNamespace($ns) {
		return \ROOT . \DS . str_replace ( "\\", \DS, $ns );
	}

	public static function delTree($dir) {
		$files = array_diff ( scandir ( $dir ), array ('.','..' ) );
		foreach ( $files as $file ) {
			(is_dir ( "$dir/$file" )) ? self::delTree ( "$dir/$file" ) : unlink ( "$dir/$file" );
		}
		return rmdir ( $dir );
	}

	public static function getLines($filename, $reverse = false, $maxLines = null, $lineCallback = null) {
		if (file_exists ( $filename )) {
			$result = [ ];
			if ($reverse && isset ( $maxLines )) {
				$fl = fopen ( $filename, "r" );
				for($x_pos = 0, $ln = 0, $lines = [ ]; fseek ( $fl, $x_pos, SEEK_END ) !== - 1; $x_pos --) {
					$char = fgetc ( $fl );
					if ($char === "\n") {
						if (is_callable ( $lineCallback )) {
							$lineCallback ( $result, $lines [$ln] );
						} else {
							$result [] = $lines [$ln];
						}
						if (isset ( $maxLines ) && sizeof ( $result ) >= $maxLines) {
							fclose ( $fl );
							return $result;
						}
						$ln ++;
						continue;
					}
					$lines [$ln] = $char . ((array_key_exists ( $ln, $lines )) ? $lines [$ln] : '');
				}
				fclose ( $fl );
				return $result;
			} else {
				$handle = fopen ( $filename, "r" );
				if ($handle) {
					while ( ($line = fgets ( $handle )) !== false ) {
						if (is_callable ( $lineCallback )) {
							$lineCallback ( $result, $line );
						} else {
							$result [] = $line;
						}
						if (isset ( $maxLines ) && sizeof ( $result ) >= $maxLines) {
							fclose ( $handle );
							if (is_array ( $result )) {
								$result = array_reverse ( $result );
							}
							return $result;
						}
					}
					fclose ( $handle );
				} else {
					// error opening the file.
				}
				if ($reverse) {
					$result = array_reverse ( $result );
				}
				return $result;
			}
		}
		return [ ];
	}
}
