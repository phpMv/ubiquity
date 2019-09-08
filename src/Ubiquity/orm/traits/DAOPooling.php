<?php

namespace Ubiquity\orm\traits;

use Ubiquity\controllers\Startup;

/**
 * Ubiquity\orm\traits$DAOPooling
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 * @property array $db
 *
 */
trait DAOPooling {

	abstract public static function startDatabase(&$config, $offset = null);
	protected static $pool;

	/**
	 * gets a new DbConnection from pool
	 *
	 * @param string $offset
	 * @return mixed
	 */
	public static function pool($offset = 'default') {
		if (! isset ( self::$db [$offset] )) {
			self::startDatabase ( Startup::$config, $offset );
		}
		return self::$db [$offset]->pool ();
	}

	public static function freePool($db) {
		self::$pool->put ( $db );
	}

	public static function go($asyncCallable, $offset = 'default') {
		$vars = \get_defined_vars ();
		\Swoole\Coroutine::create ( function () use ($vars, $asyncCallable, $offset) {
			$db = self::pool ( $offset );
			\call_user_func_array ( $asyncCallable, $vars );
			self::freePool ( $db );
		} );
	}
}

