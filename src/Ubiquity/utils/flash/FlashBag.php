<?php

namespace Ubiquity\utils\flash;

use Ubiquity\controllers\Controller;
use Ubiquity\events\EventsManager;
use Ubiquity\events\ViewEvents;
use Ubiquity\utils\http\USession;

/**
 * Bag for Session Flash messages
 * Ubiquity\utils\flash$FlashBag
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.2
 *
 */
class FlashBag implements \Iterator {
	const FLASH_BAG_KEY = '_flash_bag';
	const VAR_VIEW_NAME='flashMessages';
	private array $array;
	private int $position = 0;

	public function __construct(?Controller $controller=null) {
		$this->array = USession::get ( self::FLASH_BAG_KEY, [ ] );
		if(isset($controller)){
			$controller->getView()->setVar(self::VAR_VIEW_NAME,$this->array);
			EventsManager::addListener(ViewEvents::AFTER_RENDER,function(){
				$this->clear();
			});
		}
	}

	public function addMessage($content, $title = NULL, $type = 'info', $icon = null): void {
		$this->array [] = new FlashMessage ( $content, $title, $type, $icon );
	}

	public function addMessageAndSave($content, $title = NULL, $type = 'info', $icon = null): void  {
		$this->addMessage($content,$title,$type,$icon);
		USession::set ( self::FLASH_BAG_KEY, $this->array );
	}

	public function getMessages($type): array {
		$result = [ ];
		foreach ( $this->array as $msg ) {
			if ($msg->getType () == $type) {
				$result [] = $msg;
			}
		}
		return $result;
	}

	public function getAll(): array {
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

