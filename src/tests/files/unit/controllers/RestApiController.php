<?php

namespace controllers;

use Ubiquity\contents\normalizers\NormalizersManager;
use Ubiquity\events\EventsManager;
use eventListener\DefineLocaleEventListener;
use models\User;
use normalizer\UserNormalizer;
use models\Organization;
use normalizer\OrgaNormalizer;
use Ubiquity\orm\DAO;
use Ubiquity\controllers\rest\RestBaseController;

/**
 *
 * @route("/rest/benchmark","inherited"=>false,"automated"=>false)
 * @rest("resource"=>"")
 */
class RestApiController extends RestBaseController {

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

	public function testNormalizationDatas() {
		$orgas = DAO::getAll ( Organization::class, '', [ 'users' ] );
		$datas = NormalizersManager::normalizeArray ( $orgas, new OrgaNormalizer () );
		echo $this->_getResponseFormatter ()->toJson ( $datas );
	}

	public function testNormalizationData() {
		$orga = DAO::getOne ( Organization::class, 'name= ?', true, [ 'Conservatoire National des Arts et métiers' ] );
		NormalizersManager::registerClasses ( [ User::class => UserNormalizer::class,Organization::class => OrgaNormalizer::class ] );
		$datas = NormalizersManager::normalize_ ( $orga );
		echo $this->_getResponseFormatter ()->toJson ( $datas );
	}
}

