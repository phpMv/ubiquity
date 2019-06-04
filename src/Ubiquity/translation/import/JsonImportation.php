<?php
namespace Ubiquity\translation\import;

class JsonImportation extends AbstractImportation {

	public function load() {
		$content = file_get_contents($this->file);
		return json_decode($content);
	}
}

