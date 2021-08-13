<?php

namespace Ubiquity\controllers\rest\formatters;

use Ubiquity\controllers\rest\formatters\ResponseFormatter;
use Ubiquity\orm\OrmUtils;
use Ubiquity\cache\ClassUtils;

/**
 * JsonAPI Formatter.
 * Ubiquity\controllers\rest\formatters$JsonApiResponseFormatter
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.1.1
 * @since Ubiquity 2.0.11
 */
class JsonApiResponseFormatter extends ResponseFormatter {
	private $selfLink = "%baseRoute%/%classname%/%id%/";
	private $relationLink = "%baseRoute%/%classname%/%id%/%member%/";
	private $pageLink = "%baseRoute%/%classname%/?page[number]=%pageNumber%&page[size]=%pageSize%";
	private $baseRoute;
	private $_included;

	public function __construct($baseRoute = "") {
		$this->baseRoute = $baseRoute;
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\controllers\rest\formatters\ResponseFormatter::cleanRestObject()
	 */
	public function cleanRestObject($o, &$classname = null) {
		$pk = OrmUtils::getFirstKeyValue ( $o );
		$classname = get_class ( $o );
		$frontClassname = $this->getFrontClassname ( $classname );
		$r = [ 'id' => $pk,'type' => $frontClassname ];
		$r ['attributes'] = $o->_rest;
		$fieldsInRelations = OrmUtils::getRelationInfos ( $classname );

		$this->addSelfLink ( $r, $pk, $frontClassname );
		$o = $o->_rest;
		foreach ( $o as $k => $v ) {
			if (isset ( $fieldsInRelations [$k] )) {
				$rClassname = $fieldsInRelations [$k] ['className'] ?? $fieldsInRelations [$k] ['targetEntity'];
				$rFrontClassname = $this->getFrontClassname ( $rClassname );
				$member = $fieldsInRelations [$k] ['member'] ?? $k;
				if (isset ( $v->_rest )) {
					$pkf = OrmUtils::getFirstKey ( $rClassname );
					$r ['relationships'] [$member] ['data'] = [ 'id' => $v->_rest [$pkf],'type' => $rFrontClassname ];
					$this->_included [] = $this->cleanRestObject ( $v );
				} elseif (\is_array ( $v )) {
					foreach ( $v as $index => $value ) {
						if (isset ( $value->_rest )) {
							$this->_included [] = $this->cleanRestObject ( $value );
						}
						$pkf = OrmUtils::getFirstKey ( $rClassname );
						$r ['relationships'] [$member] ['data'] [] = [ 'id' => $value->_rest [$pkf],'type' => $rFrontClassname ];
					}
				} else {
					if (isset ( $v )) {
						$r ['relationships'] [$member] ['data'] = [ 'id' => $v,'type' => $rFrontClassname ];
						$this->addRelationshipsLink ( $r, $pk, $v, $frontClassname, $rFrontClassname, $member );
					}
				}

				unset ( $r ['attributes'] [$member] );
				unset ( $r ['attributes'] [$k] );
			}
		}
		unset ( $r ['attributes'] ['id'] );
		unset ( $r ['attributes'] ['_rest'] );
		return $r;
	}

	protected function addSelfLink(&$r, $pk, $frontClassname) {
		$r ['links'] ['self'] = $this->getLink ( $this->selfLink, [ "baseRoute" => $this->baseRoute,'id' => $pk,'classname' => $frontClassname ] );
	}

	/**
	 * Adds page links
	 *
	 * @param array $r
	 * @param string $classname
	 * @param array $pages
	 */
	protected function addPageLinks(&$r, $classname, $pages) {
		$pageSize = $pages ['pageSize'];
		unset ( $pages ['pageSize'] );
		foreach ( $pages as $page => $number ) {
			$r ['links'] [$page] = $this->getLink ( $this->pageLink, [ "baseRoute" => $this->baseRoute,'classname' => $classname,'pageNumber' => $number,'pageSize' => $pageSize ] );
		}
	}

	protected function addRelationshipsLink(&$r, $pk, $pkMember, $frontClassname, $rFrontClassname, $member) {
		$r ['relationships'] [$member] ['links'] = [ $this->getLink ( $this->relationLink, [ "baseRoute" => $this->baseRoute,'id' => $pk,'member' => $member,'classname' => $frontClassname ] ),$this->getLink ( $this->selfLink, [ "baseRoute" => $this->baseRoute,'id' => $pkMember,'classname' => $rFrontClassname ] ) ];
	}

	private function getLink($pattern, $params = [ ]) {
		$r = $pattern;
		foreach ( $params as $k => $v ) {
			$r = \str_replace ( '%' . $k . '%', $v, $r );
		}
		return $r;
	}

	private function getFrontClassname($classname) {
		return \lcfirst ( ClassUtils::getClassSimpleName ( $classname ) );
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\controllers\rest\formatters\ResponseFormatter::get()
	 */
	public function get($objects, $pages = null) {
		$objects = $this->getDatas ( $objects, $classname );
		$r = [ 'data' => $objects ];
		if ($this->_included) {
			$r ['included'] = $this->_included;
		}
		if (isset ( $pages ) && \count ( $objects ) > 0) {
			$this->addPageLinks ( $r, $this->getFrontClassname ( $classname ), $pages );
		}
		return $this->format ( $r );
	}
	
	/**
	 *
	 * @param object $object
	 * @return string
	 */
	public function getOne($object) {
		$r = [ 'data' => $this->cleanRestObject ( $object ) ];
		if ($this->_included) {
			$r['included'] = $this->_included;
		}
		return $this->format ( $r );
	}
}

