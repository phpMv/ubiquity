<?php

namespace Ubiquity\views\engine;

/**
 * Abstract template engine.
 * Ubiquity\views\engine$TemplateEngine
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
abstract class TemplateEngine {

	/**
	 * Renders a view.
	 *
	 * @param string $fileName
	 * @param array|null $pData
	 * @param boolean $asString
	 */
	abstract public function render($fileName, $pData, $asString);

	/**
	 * Returns the defined block names.
	 *
	 * @param string $templateName
	 */
	abstract public function getBlockNames($templateName);

	/**
	 * Returns the source code of the template
	 *
	 * @param string $templateName
	 */
	abstract public function getCode($templateName);
}
