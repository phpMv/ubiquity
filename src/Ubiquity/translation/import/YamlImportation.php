<?php
namespace Ubiquity\translation\import;

class YamlImportation extends AbstractImportation {

	public function load() {
		$content = \file_get_contents($this->file);
		$lines = \explode("\n", $content);
		$result = [];
		foreach ($lines as $line) {
			$line = \trim($line);
			if (\substr($line, 0, \strlen('#')) !== '#') {
				$kv = \explode(':', $line);
				if (\count($kv) == 2) {
					$result[\trim($kv[0])] = \trim($kv[1]);
				}
			}
		}
		return $result;
	}
}

