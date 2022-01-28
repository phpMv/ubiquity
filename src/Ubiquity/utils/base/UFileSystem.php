<?php

namespace Ubiquity\utils\base;

use Ubiquity\utils\base\traits\UFileSystemWriter;

/**
 * File system utilities
 * Ubiquity\utils\base$UFileSystem
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.4
 *
 */
class UFileSystem {
	use UFileSystemWriter;

	/**
	 * Find recursively pathnames matching a pattern
	 *
	 * @param string $pattern
	 * @param integer $flags
	 * @return array
	 */
	public static function glob_recursive($pattern, $flags = 0) {
		$files = \glob ( $pattern, $flags );
		foreach ( \glob ( \dirname ( $pattern ) . '/*', GLOB_ONLYDIR | GLOB_NOSORT ) as $dir ) {
			$files = \array_merge ( $files, self::glob_recursive ( $dir . '/' . \basename ( $pattern ), $flags ) );
		}
		return $files;
	}

	/**
	 * Deletes all files from a folder (not in subfolders)
	 *
	 * @param string $folder
	 * @param string $mask
	 */
	public static function deleteAllFilesFromFolder($folder, $mask = '*') {
		$files = \glob ( $folder . \DS . $mask );
		foreach ( $files as $file ) {
			if (\is_file ( $file ))
				\unlink ( $file );
		}
	}

	/**
	 * Deletes a file, in safe mode
	 *
	 * @param string $filename
	 * @return boolean
	 */
	public static function deleteFile($filename) {
		if (\file_exists ( $filename ))
			return \unlink ( $filename );
		return false;
	}

	/**
	 * Tests the existance and eventually creates a directory
	 *
	 * @param string $dir
	 * @param int $mode
	 * @param boolean $recursive
	 * @return boolean
	 */
	public static function safeMkdir($dir, $mode = 0777, $recursive = true) {
		if (! \is_dir ( $dir ))
			return \mkdir ( $dir, $mode, $recursive );
		return true;
	}

	/**
	 * Cleans a directory path by removing double backslashes or slashes and using DIRECTORY_SEPARATOR
	 *
	 * @param string $path
	 * @return string
	 */
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
		if (\file_exists ( $path )) {
			return \realpath ( $path );
		}
		return $path;
	}

	/**
	 * Cleans a file path by removing double backslashes or slashes and using DIRECTORY_SEPARATOR
	 *
	 * @param string $path
	 * @return string
	 */
	public static function cleanFilePathname($path) {
		if (UString::isNotNull ( $path )) {
			if (\DS === "/")
				$path = \str_replace ( "\\", \DS, $path );
			else
				$path = \str_replace ( "/", \DS, $path );
			$path = \str_replace ( \DS . \DS, \DS, $path );
		}
		if (\file_exists ( $path )) {
			return \realpath ( $path );
		}
		return $path;
	}

	/**
	 * Try to require a file, in safe mode
	 *
	 * @param string $file
	 * @return boolean
	 */
	public static function tryToRequire($file) {
		if (\file_exists ( $file )) {
			require_once ($file);
			return true;
		}
		return false;
	}

	/**
	 * Gets file modification time
	 *
	 * @param string $filename
	 * @return number
	 */
	public static function lastModified($filename) {
		return \filemtime ( $filename );
	}

	/**
	 * Reads entire file into a string in safe mode
	 *
	 * @param string $filename
	 * @return string|boolean
	 */
	public static function load($filename) {
		if (\file_exists ( $filename )) {
			return \file_get_contents ( $filename );
		}
		return false;
	}

	/**
	 * Returns the directory base on ROOT, corresponding to a namespace
	 *
	 * @param string $ns
	 * @return string
	 */
	public static function getDirFromNamespace($ns) {
		return \ROOT . \DS . \str_replace ( "\\", \DS, $ns );
	}

	/**
	 * Deletes recursivly a folder and its content
	 *
	 * @param string $dir
	 * @return boolean
	 */
	public static function delTree($dir) {
		$files = \array_diff ( scandir ( $dir ), array ('.','..' ) );
		foreach ( $files as $file ) {
			(\is_dir ( "$dir/$file" )) ? self::delTree ( "$dir/$file" ) : \unlink ( "$dir/$file" );
		}
		return \rmdir ( $dir );
	}

	/**
	 * Returns the lines of a file in an array
	 *
	 * @param string $filename
	 * @param boolean $reverse
	 * @param null|int $maxLines
	 * @param callback $lineCallback
	 * @return array
	 */
	public static function getLines($filename, $reverse = false, $maxLines = null, $lineCallback = null) {
		if (\file_exists ( $filename )) {
			if ($reverse && isset ( $maxLines )) {
				$result = [ ];
				$fl = \fopen ( $filename, "r" );
				for($x_pos = 0, $ln = 0, $lines = [ ]; \fseek ( $fl, $x_pos, SEEK_END ) !== - 1; $x_pos --) {
					$char = \fgetc ( $fl );
					if ($char === "\n") {
						if (\is_callable ( $lineCallback )) {
							$lineCallback ( $result, $lines [$ln] );
						} else {
							$result [] = $lines [$ln];
						}
						if (isset ( $maxLines ) && \count ( $result ) >= $maxLines) {
							\fclose ( $fl );
							return $result;
						}
						$ln ++;
						continue;
					}
					$lines [$ln] = $char . ($lines [$ln] ?? '');
				}
				\fclose ( $fl );
				return $result;
			} else {
				return self::getLinesByLine ( $filename, $reverse, $maxLines, $lineCallback );
			}
		}
		return [ ];
	}

	/**
	 * Returns relative path between two sources
	 *
	 * @param $from
	 * @param $to
	 * @param string $separator
	 * @return string
	 */
	public static function relativePath($from, $to, $separator = DIRECTORY_SEPARATOR) {
		$from = self::cleanPathname ( $from );
		$to = self::cleanPathname ( $to );

		$arFrom = \explode ( $separator, \rtrim ( $from, $separator ) );
		$arTo = \explode ( $separator, \rtrim ( $to, $separator ) );
		while ( \count ( $arFrom ) && \count ( $arTo ) && ($arFrom [0] == $arTo [0]) ) {
			\array_shift ( $arFrom );
			\array_shift ( $arTo );
		}
		return str_pad ( "", \count ( $arTo ) * 3, '..' . $separator ) . \implode ( $separator, $arFrom );
	}

	protected static function getLinesByLine($filename, $reverse, $maxLines, $lineCallback) {
		$result = [ ];
		$handle = \fopen ( $filename, "r" );
		if ($handle) {
			while ( ($line = \fgets ( $handle )) !== false ) {
				if (\is_callable ( $lineCallback )) {
					$lineCallback ( $result, $line );
				} else {
					$result [] = $line;
				}
				if (isset ( $maxLines ) && \count ( $result ) >= $maxLines) {
					\fclose ( $handle );
					if (is_array ( $result ) && $reverse) {
						$result = \array_reverse ( $result );
					}
					return $result;
				}
			}
			\fclose ( $handle );
		} else {
			// error opening the file.
		}
		if ($reverse) {
			$result = \array_reverse ( $result );
		}
		return $result;
	}
}
