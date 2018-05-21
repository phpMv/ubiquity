<?php

namespace Ubiquity\views\engine;

abstract class TemplateEngine {

	abstract public function render($fileName, $pData, $asString);
	abstract public function getBlockNames($templateName);
	abstract public function getCode($templateName);
}
