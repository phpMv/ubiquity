<?php
namespace controllers\auth\files;

use Ubiquity\controllers\auth\AuthFiles;
 /**
  * Class MyAuthentificationFiles
  */
class MyAuthentificationFiles extends AuthFiles{
	public function getViewIndex(): string{
		return "MyAuthentification/index.html";
	}

	public function getViewStepTwo(): string {
		return 'MyAuthentification/stepTwo.html';
	}


}
