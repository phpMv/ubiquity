<?php
namespace controllers;

 use repositories\UserRepository;
 use Ubiquity\attributes\items\di\Autowired;
 use Ubiquity\attributes\items\router\NoRoute;
 use Ubiquity\attributes\items\router\Route;

 /**
  * Controller UsersController
  * @route("users","automated"=>true,"inherited"=>true)
  */
 #[Route('users', inherited: true, automated: true)]
class UsersController extends \controllers\ControllerBase{

	/**
	 * @autowired
	 */
	#[Autowired]
	private UserRepository $userRepo;

	public function index(){
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
}
