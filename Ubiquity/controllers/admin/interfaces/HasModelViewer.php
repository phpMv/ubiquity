<?php

namespace Ubiquity\controllers\admin\interfaces;

use Ubiquity\controllers\admin\UbiquityMyAdminData;

interface HasModelViewer {
	public function _getAdminData ():UbiquityMyAdminData;
	public function _getBaseRoute();
}

