<?php

namespace Ubiquity\views\engine\latte;

class LatteTemplateGenerator extends \Ubiquity\views\engine\TemplateGenerator {

	private function replaceSpe(string $code): string {
		return \str_replace(['$_self', '$nonce'], ['$this->getName()', '$nonce??""'], $code);
	}

	public function parseFromTwig(string $code) {
		if (\class_exists(\LatteTools\TwigConverter::class)) {
			$converter = new \LatteTools\TwigConverter();
			$code = $converter->convert($code);
			return $this->replaceSpe($code);
		}
		return '\LatteTools\TwigConverter does not exist!';
	}

}
