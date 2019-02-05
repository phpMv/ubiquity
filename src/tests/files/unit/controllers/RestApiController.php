<?php

namespace controllers;

use Ubiquity\contents\normalizers\NormalizersManager;
use Ubiquity\controllers\rest\RestController;
use Ubiquity\events\EventsManager;
use eventListener\DefineLocaleEventListener;
use models\User;
use normalizer\UserNormalizer;
use models\Organization;
use normalizer\OrgaNormalizer;
use Ubiquity\orm\DAO;

/**
 *
 * @route("/rest/benchmark","inherited"=>false,"automated"=>false)
 * @rest("resource"=>"")
 */
class RestApiController extends RestController {

	public function initialize() {
	}

	/**
	 * Returns all objects for the resource $model
	 *
	 * @route("cache"=>false)
	 */
	public function index() {
		EventsManager::trigger ( DefineLocaleEventListener::EVENT_NAME, $this->translator );
		NormalizersManager::registerClasses ( [ User::class => UserNormalizer::class,Organization::class => OrgaNormalizer::class ] );
		$orgas = DAO::getAll ( Organization::class, '', [ 'users' ] );
		$datas = NormalizersManager::normalizeArray_ ( $orgas );
		echo $this->_getResponseFormatter ()->toJson ( $datas );
	}
}

