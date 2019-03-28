<?php

namespace eventListener;

use Ubiquity\events\EventListenerInterface;
use Ubiquity\utils\base\UArray;

class TracePageEventListener implements EventListenerInterface {
	const EVENT_NAME = 'tracePage';

	public function on(&...$params) {
		$filename = \ROOT . \DS . 'config\stats.php';
		$stats = [ ];
		if (file_exists ( $filename )) {
			$stats = include $filename;
		}
		$page = $params [0] . '::' . $params [1];
		$value = $stats [$page] ?? 0;
		$value ++;
		$stats [$page] = $value;
		UArray::save ( $stats, $filename );
	}
}
