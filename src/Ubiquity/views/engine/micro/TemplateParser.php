<?php

namespace Ubiquity\views\engine\micro;

/**
 * Micro template engine parser
 * Ubiquity\views\engine\micro$TemplateParser
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.1
 *
 */
class TemplateParser {
	private $fileContent;

	public function __construct($fileName) {
		$this->fileContent = $this->parse ( \file_get_contents ( $fileName ) );
	}

	private function parse($html) {
		$startPoint = '{{';
		$endPoint = '}}';
		$result = \preg_replace ( '/(' . \preg_quote ( $startPoint ) . ')(.*?)(' . \preg_quote ( $endPoint ) . ')/sim', '<?php echo $2 ?>', $html );
		return $result;
	}

	public function __toString() {
		return $this->fileContent;
	}
}
