<?php

namespace Ubiquity\utils\flash;

use Ubiquity\utils\http\USession;

/**
 * Bag for Session Flash messages
 * Ubiquity\utils\flash$FlashBag
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.1
 *
 */
class FlashBag implements \Iterator {
	const FLASH_BAG_KEY = "_flash_bag";
	private $array;
	private $position = 0;

	public function __construct() {
		USession::start ();
		$this->array = USession::get ( self::FLASH_BAG_KEY, [ ] );
	}

	public function addMessage($content, $title = NULL, $type = "info", $icon = null) {
		$this->array [] = new FlashMessage ( $content, $title, $type, $icon );
	}

	public function getMessages($type) {
		$result = [ ];
		foreach ( $this->array as $msg ) {
			if ($msg->getType () == $type)
				$result [] = $msg;
		}
		return $result;
	}

	public function getAll() {
		return $this->array;
	}

	public function clear() {
		$this->array = [ ];
		USession::delete ( self::FLASH_BAG_KEY );
	}

	public function rewind() {
		$this->position = 0;
	}

	/**
	 *
	 * @return FlashMessage
	 */
	public function current() {
		return $this->array [$this->position];
	}

	public function key() {
		return $this->position;
	}

	public function next() {
		++ $this->position;
	}

	public function valid() {
		return isset ( $this->array [$this->position] );
	}

	public function save() {
		$this->array = USession::set ( self::FLASH_BAG_KEY, $this->array );
	}
}

