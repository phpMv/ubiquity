<?php
namespace controllers\auth\files;

use Ubiquity\controllers\auth\AuthFiles;
 /**
 * Class TestAuthControllerFiles
 **/
class TestAuthControllerFiles extends AuthFiles{
	public function getViewIndex(){
		return "TestAuthController/index.html";
	}

	public function getViewInfo(){
		return "TestAuthController/info.html";
	}

	public function getViewNoAccess(){
		return "TestAuthController/noAccess.html";
	}

	public function getViewDisconnected(){
		return "TestAuthController/disconnected.html";
	}

	public function getViewMessage(){
		return "TestAuthController/message.html";
	}

	public function getViewBaseTemplate(){
		return "TestAuthController/baseTemplate.html";
	}


}
