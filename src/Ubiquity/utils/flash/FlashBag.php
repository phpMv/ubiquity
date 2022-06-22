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
	private bool $autoClear;

	public function __construct(bool $autoClear=true) {
		$this->array = USession::get ( self::FLASH_BAG_KEY, [ ] );
		$this->autoClear=$autoClear;
		EventsManager::addListener(ViewEvents::BEFORE_RENDER,function($_, &$data) {
			$data[self::VAR_VIEW_NAME]=$this->array;
		});
		EventsManager::addListener(ViewEvents::AFTER_RENDER,function(){
			if($this->autoClear) {
				$this->clear();
			}
		});
	}

	/**
	 * Adds a temporary new message to the bag.
	 * @param string $content
	 * @param string|null $title
	 * @param string $type
	 * @param string|null $icon
	 */
	public function addMessage(string $content, string $title = NULL, string $type = 'info', string $icon = null): void {
		$this->array [] = new FlashMessage ( $content, $title, $type, $icon );
	}

	/**
	 * Adds and saves a message in the bag.
	 * @param string $content
	 * @param string|null $title
	 * @param string $type
	 * @param string|null $icon
	 */
	public function addMessageAndSave(string $content, string $title = NULL, string $type = 'info', string $icon = null): void  {
		$this->addMessage($content,$title,$type,$icon);
		USession::set ( self::FLASH_BAG_KEY, $this->array );
	}

	/**
	 * Returns all the message of a type in the bag.
	 * @param string $type
	 * @return FlashMessage[]
	 */
	public function getMessages(string $type): array {
		$result = [ ];
		foreach ( $this->array as $msg ) {
			if ($msg->getType () == $type) {
				$result [] = $msg;
			}
		}
		return $result;
	}

	/**
	 * Returns all the messages.
	 * @return array
	 */
	public function getAll(): array {
		return $this->array;
	}

	/**
	 * Clears the bag.
	 */
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
	public function current(): FlashMessage {
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

	/**
	 * @return bool
	 */
	public function isAutoClear(): bool {
		return $this->autoClear;
	}

	/**
	 * @param bool $autoClear
	 */
	public function setAutoClear(bool $autoClear): void {
		$this->autoClear = $autoClear;
	}
}

