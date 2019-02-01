<?php

namespace Ubiquity\core\postinstall;

use Ubiquity\core\Framework;

class Display {

	public static function semanticMenu($id, $semantic) {
		$menu=$semantic->htmlMenu($id, [ "Ubiquity website","Guide","Doc API","GitHub" ]);
		$menu->asLinks([ "https://ubiquity.kobject.net","https://micro-framework.readthedocs.io/en/latest/?badge=latest","https://api.kobject.net/ubiquity/","https://github.com/phpMv/ubiquity" ], 'new');
		$menu->setSecondary();
		if (Framework::hasAdmin()) {
			$menu->addItem(new \Ajax\semantic\html\elements\html5\HtmlLink("", "Admin", "UbiquityMyAdmin", "admin"));
		}
		return $menu;
	}
}

