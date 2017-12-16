<?php

namespace Ubiquity\views\engine;

abstract class TemplateEngine {

	abstract public function render($fileName, $pData, $asString);
}
