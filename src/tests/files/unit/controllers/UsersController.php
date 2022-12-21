<?php

namespace controllers;

use models\Organization;
use models\User;
use repositories\UserRepository;
use Ubiquity\attributes\items\di\Autowired;
use Ubiquity\attributes\items\router\Get;
use Ubiquity\attributes\items\router\NoRoute;
use Ubiquity\attributes\items\router\Route;
use Ubiquity\orm\DAO;

/**
 * Controller UsersController
 * @route("users","automated"=>true,"inherited"=>true)
 */
#[Route('users', inherited: true, automated: true)]
class UsersController extends \controllers\ControllerBase {

	/**
	 * @autowired
	 */
	#[Autowired]
	private UserRepository $userRepo;

	/**
	 * @get("priority"=>0)
	 */
	#[Get]
	public function index() {
		$this->userRepo->all();
		$this->loadView("UsersController/index.html");
	}

	/**
	 * @param UserRepository $userRepo
	 * @noRoute
	 */
	#[NoRoute]
	public function setUserRepo(UserRepository $userRepo): void {
		$this->userRepo = $userRepo;

	}

	/**
	 * @get("/{firstname}","name"=>"users.one","priority"=>4)
	 */
	#[Get(path: "/{firstname}", name: "users.one", priority: 4)]
	public function one($firstname) {
		$this->userRepo->one('firstname= ?', false, [$firstname]);
		$this->loadView('UsersController/one.html');
	}

	/**
	 * @get("{id}","priority"=>6)
	 */
	#[Get(path: "{id}", name: "users.byId", priority: 6)]
	public function byId(int $id) {
		$this->userRepo->byId($id);
		$this->loadView('UsersController/byId.html');
	}

	/**
	 * @get("insert/{firstname}/{lastname}","priority"=>5)
	 */
	#[Get(path: "insert/{firstname}/{lastname}", name: "users.insert", priority: 5)]
	public function insertAndDelete(string $firstname, string $lastname) {
		$user = new User();
		$user->setFirstname($firstname);
		$user->setLastname($lastname);
		$orga = DAO::getOne(Organization::class, '1=1', false);
		$user->setOrganization($orga);
		$this->userRepo->insert($user, false, 'one');
		$this->loadView('UsersController/one.html');
		$this->userRepo->remove($user, 'one');
		$this->loadView('UsersController/one.html');
	}
}
