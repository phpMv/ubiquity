<?php

namespace Ubiquity\orm\traits;

use Ubiquity\controllers\Startup;
use Ubiquity\exceptions\DAOException;

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

	abstract public static function getDbOffset(&$config, $offset = null);
	protected static $pool;

	/**
	 * Initialize pooling (To invoke during Swoole startup)
	 *
	 * @param array $config
	 * @param ?string $offset
	 * @param int $size
	 */
	public static function initPooling(&$config, $offset = null, int $size = 16) {
		$dbConfig = self::getDbOffset ( $config, $offset );
		$wrapperClass = $dbConfig ['wrapper'] ?? \Ubiquity\db\providers\pdo\PDOWrapper::class;
		if (\method_exists ( $wrapperClass, 'getPoolClass' )) {
			$poolClass = \call_user_func ( $wrapperClass . '::getPoolClass' );
			if (\class_exists ( $poolClass, true )) {
				$reflection_class = new \ReflectionClass ( $poolClass );
				self::$pool = $reflection_class->newInstanceArgs ( [ &$config,$offset,$size ] );
			} else {
				throw new DAOException ( $poolClass . ' class does not exists!' );
			}
		} else {
			throw new DAOException ( $wrapperClass . ' does not support connection pooling!' );
		}
		self::startDatabase ( $config, $offset );
	}

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

