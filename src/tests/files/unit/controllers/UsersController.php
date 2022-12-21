<?php

namespace controllers;

use repositories\UserRepository;
use Ubiquity\attributes\items\di\Autowired;
use Ubiquity\attributes\items\router\Get;
use Ubiquity\attributes\items\router\NoRoute;
use Ubiquity\attributes\items\router\Route;

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
	 * @return void
	 * @throws \Exception
	 * @get()
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
	 * @param $firstname
	 * @return void
	 * @throws \Exception
	 * @route("/{firstname}","name"=>"users.one","priority"=>5)
	 */
	#[Get(path: "/{firstname}", name: "users.one", priority: 5)]
	public function one($firstname) {
		$this->userRepo->one('firstname= ?', false, [$firstname]);
		$this->loadView('UsersController/one.html');
	}


	/**
	 * @param int $id
	 * @return void
	 * @throws \Exception
	 * @route("{id}","priority"=>6)
	 */
	#[Get(path: "{id}", name: "users.byId", priority: 6)]
	public function byId(int $id) {
		$this->userRepo->byId($id);
		$this->loadView('UsersController/byId.html');
	}

}
