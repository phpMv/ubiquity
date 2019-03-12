<?php

namespace Ubiquity\scaffolding\creators;

/**
 * Creates a Rest controller associated with a resource
 * Ubiquity\scaffolding\creators$RestControllerCreator
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.2
 *
 */
class RestControllerCreator extends RestApiControllerCreator {
	private $resource;

	public function __construct($restControllerName, $resource, $routePath = '') {
		parent::__construct ( $restControllerName, $routePath );
		$this->resource = $resource;
		$this->templateName = 'restController.tpl';
	}

	protected function addVariablesForReplacement(&$variables) {
		$variables ["%resource%"] = $this->resource;
	}

	protected function addViews(&$uses, &$messages, &$classContent) {
	}
}

