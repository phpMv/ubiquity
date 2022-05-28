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

	public function __construct() {
		$this->array = USession::get ( self::FLASH_BAG_KEY, [ ] );
		EventsManager::addListener(ViewEvents::BEFORE_RENDER,function($_, &$data) {
			$data[self::VAR_VIEW_NAME]=$this->array;
		});
		EventsManager::addListener(ViewEvents::AFTER_RENDER,function(){
			$this->clear();
		});
	}

	public function addMessage(string $content, string $title = NULL, string $type = 'info', string $icon = null): void {
		$this->array [] = new FlashMessage ( $content, $title, $type, $icon );
	}

	public function addMessageAndSave(string $content, string $title = NULL, string $type = 'info', string $icon = null): void  {
		$this->addMessage($content,$title,$type,$icon);
		USession::set ( self::FLASH_BAG_KEY, $this->array );
	}

	public function getMessages(string $type): array {
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

	public function clear(): void {
		$this->array = [ ];
		USession::delete ( self::FLASH_BAG_KEY );
	}

	public function rewind(): void {
		$this->position = 0;
	}

	/**
	 *
	 * @return FlashMessage
	 */
	public function current(): mixed {
		return $this->array [$this->position];
	}

	public function key(): int {
		return $this->position;
	}

	public function next(): void {
		++ $this->position;
	}

	public function valid(): bool {
		return isset ( $this->array [$this->position] );
	}

	public function save(): void {
		$this->array = USession::set ( self::FLASH_BAG_KEY, $this->array );
	}
}

