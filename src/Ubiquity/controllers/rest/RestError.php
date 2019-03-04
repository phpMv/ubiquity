<?php

namespace Ubiquity\controllers\rest;

class RestError {
	private $status;
	private $code;
	private $source;
	private $title;
	private $detail;

	public function __construct($code, $title, $detail = null, $source = null, $status = null) {
		$this->code = $code;
		$this->title = $title;
		$this->detail = $detail;
		$this->source = $source;
		$this->status = $status;
	}

	/**
	 *
	 * @return string
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getCode() {
		return $this->code;
	}

	/**
	 *
	 * @return string
	 */
	public function getSource() {
		return $this->source;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 *
	 * @return string
	 */
	public function getDetail() {
		return $this->detail;
	}

	/**
	 *
	 * @param string $status
	 */
	public function setStatus($status) {
		$this->status = $status;
	}

	/**
	 *
	 * @param mixed $code
	 */
	public function setCode($code) {
		$this->code = $code;
	}

	/**
	 *
	 * @param string $source
	 */
	public function setSource($source) {
		$this->source = $source;
	}

	/**
	 *
	 * @param mixed $title
	 */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
	 *
	 * @param string $detail
	 */
	public function setDetail($detail) {
		$this->detail = $detail;
	}

	public function asArray() {
		$r = [ ];
		if (isset ( $this->code )) {
			$r ['code'] = $this->code;
		}
		if (isset ( $this->status )) {
			$r ['status'] = $this->status;
		}
		if (isset ( $this->source )) {
			$r ['source'] = [ 'pointer' => $this->source ];
		}
		if (isset ( $this->title )) {
			$r ['title'] = $this->title;
		}
		if (isset ( $this->detail )) {
			$r ['detail'] = $this->detail;
		}
		return $r;
	}
	
	public static function notFound($keyValues,$source=null){
		return new RestError(404, "No result found","No result found for primary key(s): ".$keyValues,$source,404);
	}
}

