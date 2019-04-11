<?php

namespace Ubiquity\contents\transformation;

use Ubiquity\cache\CacheManager;
use Ubiquity\contents\transformation\transformers\DateTime;
use Ubiquity\contents\transformation\transformers\FirstUpperCase;
use Ubiquity\contents\transformation\transformers\LowerCase;
use Ubiquity\contents\transformation\transformers\Md5;
use Ubiquity\contents\transformation\transformers\Password;
use Ubiquity\contents\transformation\transformers\UpperCase;
use Ubiquity\orm\DAO;
use Ubiquity\orm\OrmUtils;
use Ubiquity\utils\base\UArray;

/**
 * Transform objects after loading
 *
 * Ubiquity\contents\transformation\transformers$TransformersManager
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.1
 *
 */
class TransformersManager {
	const TRANSFORMER_TYPES = [ 'transform' => TransformerInterface::class,'toView' => TransformerViewInterface::class,'toForm' => TransformerFormInterface::class ];
	/**
	 *
	 * @var array|mixed
	 */
	private static $transformers = [ 'md5' => Md5::class,'datetime' => DateTime::class,'upper' => UpperCase::class,'firstUpper' => FirstUpperCase::class,'lower' => LowerCase::class,'password' => Password::class ];
	private static $key = "contents/transformers";

	/**
	 * Do not use at runtime !
	 */
	public static function start() {
		$transformers = [ ];
		if (CacheManager::$cache->exists ( self::$key )) {
			$transformers = CacheManager::$cache->fetch ( self::$key );
		}
		self::$transformers = array_merge ( self::$transformers, $transformers );
	}

	public static function startProd($op = null) {
		DAO::$useTransformers = true;
		DAO::$transformerOp = $op ?? 'transform';
	}

	/**
	 * Do not used at runtime !
	 *
	 * @param string $transformer
	 * @param string $classname
	 */
	public static function registerClass($transformer, $classname) {
		self::$transformers [$transformer] = $classname;
	}

	/**
	 * Do not used at runtime !
	 *
	 * @param string $transformer
	 * @param string $classname
	 */
	public static function registerClassAndSave($transformer, $classname) {
		self::start ();
		self::registerClass ( $transformer, $classname );
		self::store ();
	}

	/**
	 * Do not used at runtime !
	 *
	 * @param array $transformersAndClasses
	 */
	public static function registerClasses($transformersAndClasses) {
		foreach ( $transformersAndClasses as $transformer => $class ) {
			self::registerClass ( $transformer, $class );
		}
	}

	/**
	 * Do not used at runtime !
	 *
	 * @param array $transformersAndClasses
	 */
	public static function registerClassesAndSave($transformersAndClasses) {
		self::start ();
		foreach ( $transformersAndClasses as $transformer => $class ) {
			self::registerClass ( $transformer, $class );
		}
		self::store ();
	}

	public static function getTransformerClass($transformer) {
		if (isset ( self::$transformers [$transformer] )) {
			return self::$transformers [$transformer];
		}
		return null;
	}

	public static function transform($instance, $member, $transform = 'transform') {
		$getter = 'get' . $member;
		if (method_exists ( $instance, $getter )) {
			return self::applyTransformer ( $instance, $member, $instance->{$getter} (), $transform );
		}
		return null;
	}

	public static function applyTransformer($instance, $member, $value, $transform = 'transform') {
		$class = get_class ( $instance );
		$metas = OrmUtils::getModelMetadata ( $class );
		if (isset ( $metas ['#transformers'] [$transform] [$member] )) {
			$transformer = $metas ['#transformers'] [$transform] [$member];
			return $transformer::$transform ( $value );
		}
		return $value;
	}

	public static function transformInstance($instance, $transform = 'transform') {
		$class = get_class ( $instance );
		$metas = OrmUtils::getModelMetadata ( $class );
		$transformers = $metas ['#transformers'] [$transform] ?? [ ];
		foreach ( $transformers as $member => $transformer ) {
			$getter = 'get' . ucfirst ( $member );
			$setter = 'set' . ucfirst ( $member );
			if (method_exists ( $instance, $getter )) {
				$value = $transformer::$transform ( $instance->{$getter} () );
				if (method_exists ( $instance, $setter )) {
					$instance->{$setter} ( $value );
				}
				$instance->_rest [$member] = $value;
			}
		}
	}

	public static function store() {
		CacheManager::$cache->store ( self::$key, "return " . UArray::asPhpArray ( self::$transformers, 'array' ) . ';' );
	}
}

