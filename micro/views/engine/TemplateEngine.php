<?php
namespace micro\views\engine;
abstract class TemplateEngine{
	public abstract function render($fileName, $pData,$asString);
}