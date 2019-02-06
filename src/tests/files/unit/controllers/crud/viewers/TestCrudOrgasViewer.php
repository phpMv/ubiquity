<?php
namespace controllers\crud\viewers;

use Ubiquity\controllers\admin\viewers\ModelViewer;
 /**
 * Class TestCrudOrgasViewer
 **/
class TestCrudOrgasViewer extends ModelViewer{
	/**
	 * {@inheritDoc}
	 * @see \Ubiquity\controllers\admin\viewers\ModelViewer::getDataTableRowButtons()
	 */
	protected function getDataTableRowButtons() {
		return ["display","edit","delete"];
	}


}
