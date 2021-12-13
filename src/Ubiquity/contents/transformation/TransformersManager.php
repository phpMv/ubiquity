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
use Ubiquity\contents\transformation\transformers\Boolean;

/**
 * Transform objects after loading
 *
 * Ubiquity\contents\transformation\transformers$TransformersManager
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.4
 *
 */
class TransformersManager {
	const TRANSFORMER_TYPES = [ 'reverse' => TransformerInterface::class,'transform' => TransformerInterface::class,'toView' => TransformerViewInterface::class,'toForm' => TransformerFormInterface::class ];
	/**
	 *
	 * @var array|mixed
	 */
	private static $transformers = [ 'md5' => Md5::class,'datetime' => DateTime::class,'upper' => UpperCase::class,'firstUpper' => FirstUpperCase::class,'lower' => LowerCase::class,'password' => Password::class,'boolean'=>Boolean::class ];
	private static $key = 'contents/transformers';

	/**
	 * Load all transformers.
	 * Do not use at runtime !
	 */
	public static function start() {
		if (CacheManager::$cache->exists ( self::$key )) {
			self::$transformers = \array_merge ( self::$transformers, CacheManager::$cache->fetch ( self::$key ) );
		}
		if (\class_exists ( '\\Ubiquity\\security\\csrf\\CsrfManager' )) {
			self::$transformers ['crypt'] = '\\Ubiquity\\contents\\transformation\\transformers\\Crypt';
		}
	}

	/**
	 * Start the manager for production.
	 *
	 * @param ?string $op
	 */
	public static function startProd(?string $op = 'transform'): void {
		DAO::$useTransformers = true;
		DAO::$transformerOp = $op;
	}

	/**
	 * Register a new transformer class.
	 * Do not used at runtime !
	 *
	 * @param string $transformer
	 * @param string $classname
	 */
	public static function registerClass(string $transformer, string $classname): void {
		self::$transformers [$transformer] = $classname;
	}

	/**
	 * Register and save in cache a new Transformer.
	 * Do not used at runtime !
	 *
	 * @param string $transformer
	 * @param string $classname
	 */
	public static function registerClassAndSave(string $transformer, string $classname): void {
		self::start ();
		self::registerClass ( $transformer, $classname );
		self::store ();
	}

	/**
	 * Register an associative array of transformers based on ['name'=>'transformerClass'].
	 * Do not used at runtime !
	 *
	 * @param array $transformersAndClasses
	 */
	public static function registerClasses(array $transformersAndClasses): void {
		foreach ( $transformersAndClasses as $transformer => $class ) {
			self::registerClass ( $transformer, $class );
		}
	}

	/**
	 * Register and save an associative array of transformers based on ['name'=>'transformerClass'].
	 * Do not used at runtime !
	 *
	 * @param array $transformersAndClasses
	 */
	public static function registerClassesAndSave(array $transformersAndClasses): void {
		self::start ();
		foreach ( $transformersAndClasses as $transformer => $class ) {
			self::registerClass ( $transformer, $class );
		}
		self::store ();
	}

	/**
	 * Return the class from a transformer name.
	 *
	 * @param string $transformer
	 * @return ?string
	 */
	public static function getTransformerClass(string $transformer): ?string {
		if (isset ( self::$transformers [$transformer] )) {
			return self::$transformers [$transformer];
		}
		return null;
	}

	/**
	 * Transform a member of an instance.
	 *
	 * @param object $instance
	 * @param string $member
	 * @param string $transform
	 * @return ?mixed
	 */
	public static function transform(object $instance, string $member, ?string $transform = 'transform') {
		$getter = 'get' . $member;
		if (\method_exists ( $instance, $getter )) {
			return self::applyTransformer ( $instance, $member, $instance->{$getter} (), $transform );
		}
		return null;
	}

	/**
	 * Apply a transformer using a member transformer(s) on a value.
	 *
	 * @param object $instance
	 * @param string $member
	 * @param mixed $value
	 * @param string $transform
	 * @return mixed
	 */
	public static function applyTransformer(object $instance, string $member, $value, ?string $transform = 'transform') {
		$class = \get_class ( $instance );
		$metas = OrmUtils::getModelMetadata ( $class );
		if (isset ( $metas ['#transformers'] [$transform] [$member] )) {
			$transformer = $metas ['#transformers'] [$transform] [$member];
			return $transformer::$transform ( $value );
		}
		return $value;
	}

	/**
	 * Transform all the members of a model instance.
	 *
	 * @param object $instance
	 * @param string $transform
	 */
	public static function transformInstance(object $instance, $transform = 'transform'): void {
		$class = \get_class ( $instance );
		$metas = OrmUtils::getModelMetadata ( $class );
		$transformers = $metas ['#transformers'] [$transform] ?? [ ];
		foreach ( $transformers as $member => $transformer ) {
			$getter = 'get' . ucfirst ( $member );
			$setter = 'set' . ucfirst ( $member );
			if (\method_exists ( $instance, $getter )) {
				$value = $transformer::$transform ( $instance->{$getter} () );
				if (\method_exists ( $instance, $setter )) {
					$instance->{$setter} ( $value );
				}
				$instance->_rest [$member] = $value;
			}
		}
	}

	/**
	 * Store the loaded transformers in cache.
	 */
	public static function store(): void {
		CacheManager::$cache->store ( self::$key, self::$transformers );
	}
}

