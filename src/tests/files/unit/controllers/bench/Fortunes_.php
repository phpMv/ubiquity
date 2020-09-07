<?php

namespace controllers\bench;

use models\bench\Fortune;
use Ubiquity\orm\core\prepared\DAOPreparedQueryAll;
use Ubiquity\orm\DAO;
use Ubiquity\controllers\Startup;

class Fortunes_ extends \Ubiquity\controllers\SimpleViewAsyncController {
	protected static $pDao;

	public static function warmup() {
		self::$pDao = new DAOPreparedQueryAll ( Fortune::class );
	}

	public function initialize() {
		\Ubiquity\utils\http\UResponse::setContentType ( 'text/html', 'utf-8' );
		if (\php_sapi_name () == 'apache2handler') {
			DAO::startDatabase ( Startup::$config, 'bench' );
			self::warmup ();
		}
	}

	/**
	 *
	 * @route("fortunes")
	 */
	public function index() {
		$fortunes = self::$pDao->execute ();
		$fortunes [0] = new Fortune ( 0, 'Additional fortune added at request time.' );
		\usort ( $fortunes, function ($left, $right) {
			return $left->message <=> $right->message;
		} );
		$this->loadView ( 'Fortunes/index.php', [ 'fortunes' => $fortunes ] );
	}
}

