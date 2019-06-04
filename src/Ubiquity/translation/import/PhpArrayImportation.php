<?php
namespace Ubiquity\translation\import;

class PhpArrayImportation extends AbstractImportation {

	public function load() {
		return include $this->file;
	}
}

