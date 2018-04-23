<?php

namespace Ubiquity\controllers\admin\interfaces;

use Ubiquity\controllers\admin\UbiquityMyAdminData;

interface HasModelViewerInterface {
	public function _getAdminData ():UbiquityMyAdminData;
	public function _getBaseRoute();
}

