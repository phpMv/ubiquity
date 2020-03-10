<?php

namespace Ubiquity\orm\traits;

use Ubiquity\orm\OrmUtils;
use Ubiquity\orm\core\PendingRelationsRequest;
use Ubiquity\orm\parser\ManyToManyParser;
use Ubiquity\db\SqlUtils;

/**
 * Used by DAO class, prepare relations for loading.
 * Ubiquity\orm\traits$DAORelationsPrepareTrait
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.3
 *
 */
trait DAORelationsPrepareTrait {

	/**
	 * Prepares members associated with $instance with a ManyToMany type relationship
	 *
	 * @param $db
	 * @param $ret array of sql conditions
	 * @param object $instance
	 * @param string $member Member on which a ManyToMany annotation must be present
	 * @param array $annot used internally
	 */
	protected static function prepareManyToMany($db, &$ret, $instance, $member, $annot = null) {
		$class = get_class ( $instance );
		if (! isset ( $annot )) {
			$annot = OrmUtils::getAnnotationInfoMember ( $class, "#ManyToMany", $member );
		}
		if ($annot !== false) {
			$key = $annot ["targetEntity"] . "|" . $member . "|" . $annot ["inversedBy"] . "|";
			if (! isset ( $ret [$key] )) {
				$parser = new ManyToManyParser ( $db, $instance, $member );

				$parser->init ( $annot );
				$ret [$key] = $parser;
			}
			$accessor = "get" . ucfirst ( $ret [$key]->getMyPk () );
			if (method_exists ( $instance, $accessor )) {
				$fkv = $instance->$accessor ();
				$ret [$key]->addValue ( $fkv );
			}
		}
	}

	/**
	 * Prepares members associated with $instance with a oneToMany type relationship
	 *
	 * @param $ret array of sql conditions
	 * @param object $instance
	 * @param string $member Member on which a OneToMany annotation must be present
	 * @param array $annot used internally
	 */
	protected static function prepareOneToMany(&$ret, $instance, $member, $annot = null) {
		$class = get_class ( $instance );
		if (! isset ( $annot ))
			$annot = OrmUtils::getAnnotationInfoMember ( $class, "#oneToMany", $member );
		if ($annot !== false) {
			$fkAnnot = OrmUtils::getAnnotationInfoMember ( $annot ["className"], "#joinColumn", $annot ["mappedBy"] );
			if ($fkAnnot !== false) {
				$fkv = OrmUtils::getFirstKeyValue ( $instance );
				$key = $annot ["className"] . "|" . $member . "|" . $annot ["mappedBy"] . "|" . $fkAnnot ["className"];
				if (! isset ( $ret [$key] )) {
					$ret [$key] = new PendingRelationsRequest ();
				}
				$quote = SqlUtils::$quote;
				$ret [$key]->addPartObject ( $instance, $quote . $fkAnnot ["name"] . $quote . "= ?", $fkv );
			}
		}
	}

	/**
	 * Prepares members associated with $instance with a manyToOne type relationship
	 *
	 * @param $ret array of sql conditions
	 * @param object $instance
	 * @param mixed $value
	 * @param string $fkField
	 * @param array $annotationArray
	 */
	protected static function prepareManyToOne(&$ret, $instance, $value, $fkField, $annotationArray) {
		$member = $annotationArray ["member"];
		$fk = OrmUtils::getFirstKey ( $annotationArray ["className"] );
		$key = $annotationArray ["className"] . "|" . $member . "|" . $fkField . "|";
		if (! isset ( $ret [$key] )) {
			$ret [$key] = new PendingRelationsRequest ();
		}
		$ret [$key]->addPartObject ( $instance, SqlUtils::$quote . $fk . SqlUtils::$quote . "= ?", $value );
	}
}
