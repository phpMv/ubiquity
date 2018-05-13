<?php

namespace Ubiquity\controllers\admin\interfaces;


use Ubiquity\controllers\crud\CRUDDatas;

interface HasModelViewerInterface {
	public function _getAdminData ():CRUDDatas;
	public function _getBaseRoute();
	public function _getInstancesFilter($model);
}

