<?php

namespace controllers\crud\viewers;

use Ubiquity\controllers\crud\viewers\ModelViewer;

/**
 * Class TestCrudOrgasViewer
 */
class TestCrudOrgasViewer extends ModelViewer {

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\controllers\crud\viewers\ModelViewer::getDataTableRowButtons()
	 */
	protected function getDataTableRowButtons(): array {
		return [ "display","edit","delete" ];
	}
}
