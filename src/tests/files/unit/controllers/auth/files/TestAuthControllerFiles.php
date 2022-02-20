<?php
namespace controllers\auth\files;

use Ubiquity\controllers\auth\AuthFiles;
 /**
 * Class TestAuthControllerFiles
 **/
class TestAuthControllerFiles extends AuthFiles{
	public function getViewIndex():string{
		return "TestAuthController/index.html";
	}

	public function getViewInfo():string{
		return "TestAuthController/info.html";
	}

	public function getViewNoAccess():string{
		return "TestAuthController/noAccess.html";
	}

	public function getViewDisconnected():string{
		return "TestAuthController/disconnected.html";
	}

	public function getViewMessage():string{
		return "TestAuthController/message.html";
	}

	public function getViewBaseTemplate():string{
		return "TestAuthController/baseTemplate.html";
	}


}
