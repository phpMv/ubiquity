<?php
namespace micro\views\engine;
abstract class TemplateEngine{
	abstract public function render($fileName, $pData,$asString);
}
