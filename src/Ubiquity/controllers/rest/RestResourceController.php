<?php

/**
 * Rest part
 */
namespace Ubiquity\controllers\rest;

use Ubiquity\cache\CacheManager;
use Ubiquity\orm\DAO;

/**
 * Abstract base class for Rest controllers associated with a specific resource (model).
 * Ubiquity\controllers\rest$RestController
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.5
 *
 */
abstract class RestResourceController extends RestBaseController implements HasResourceInterface {

	public function initialize() {
		$thisClass = \get_class ( $this );
		if (! isset ( $this->model ))
			$this->model = CacheManager::getRestResource ( $thisClass );
		if (! isset ( $this->model )) {
			$modelsNS = $this->config ["mvcNS"] ["models"];
			$this->model = $modelsNS . "\\" . $this->_getResponseFormatter ()->getModel ( $thisClass );
		}
		parent::initialize ();
	}

	/**
	 * Returns the template for creating this type of controller
	 *
	 * @return string
	 */
	public static function _getTemplateFile() {
		return 'restResourceController.tpl';
	}
}
