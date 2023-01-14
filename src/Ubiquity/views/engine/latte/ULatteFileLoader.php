<?php

namespace Ubiquity\views\engine\latte;

class ULatteFileLoader extends \Latte\Loaders\FileLoader {

	private array $namespaces=[];

	private function getFilename(string $fileName): string {
		if (\preg_match('/\@(.*?)\//', $fileName, $match) == 1) {
			$ns='@'.$match[1];
			if ($dir=$this->namespaces[$ns]??false) {
				return \realpath(\str_replace($ns,$dir,$fileName));
			}
		}
		return $this->baseDir.$fileName;
	}

	public function addPath($path, $namespace): void {
		$this->namespaces['@'.\ltrim($namespace,'@')]=$path;
	}

	public function getContent(string $fileName): string {
		$file=$this->getFilename($fileName);
		return \file_get_contents($file);
	}

	/**
	 * Returns unique identifier for caching.
	 */
	public function getUniqueId(string $file): string {
		return $this->getFilename($file);
	}
}