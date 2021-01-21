<?php

namespace controllers\bench;

use Ubiquity\controllers\Startup;
use Ubiquity\orm\DAO;
use Ubiquity\orm\core\prepared\DAOPreparedQueryById;

/**
 * Bench controller.
 */
class DbMy extends \Ubiquity\controllers\Controller {

	/**
	 *
	 * @var DAOPreparedQueryById
	 */
	protected static $pDao;

	public function __construct() {
	}

	public function initialize() {
		\Ubiquity\utils\http\UResponse::setContentType ( 'application/json' );
		if (\php_sapi_name () == 'apache2handler') {
			DAO::startDatabase ( Startup::$config, 'bench' );
			self::warmup ();
		}
	}

	public static function warmup() {
		self::$pDao = new DAOPreparedQueryById ( \models\bench\World::class );
	}

	public function getCount($queries) {
		$count = 1;
		if ($queries > 1) {
			if (($count = $queries) > 500) {
				$count = 500;
			}
		}
		return $count;
	}

	/**
	 *
	 * @route("db")
	 */
	public function index() {
		echo \json_encode ( self::$pDao->execute ( [ 'id' => \mt_rand ( 1, 10000 ) ] )->_rest );
	}

	/**
	 *
	 * @route("db/query/{queries}")
	 */
	public function query($queries = 1) {
		$worlds = [ ];
		$count = $this->getCount ( $queries );

		while ( $count -- ) {
			$worlds [] = (self::$pDao->execute ( [ 'id' => \mt_rand ( 1, 10000 ) ] ))->_rest;
		}
		echo \json_encode ( $worlds );
	}

	/**
	 *
	 * @route("db/update/{queries}")
	 */
	public function update($queries = 1) {
		$worlds = [ ];
		$count = $this->getCount ( $queries );
		$ids = $this->getUniqueRandomNumbers ( $count );
		foreach ( $ids as $id ) {
			$world = self::$pDao->execute ( [ 'id' => $id ] );
			$world->randomNumber = \mt_rand ( 1, 10000 );
			DAO::toUpdate ( $world );
			$worlds [] = $world->_rest;
		}
		DAO::updateGroups ( $count );
		echo \json_encode ( $worlds );
	}

	private function getUniqueRandomNumbers($count) {
		$res = [ ];
		do {
			$res [\mt_rand ( 1, 10000 )] = 1;
		} while ( \count ( $res ) < $count );

		\ksort ( $res );

		return \array_keys ( $res );
	}
}
