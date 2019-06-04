<?php
namespace Ubiquity\translation\import;

use Ubiquity\translation\MessagesUpdates;
use Ubiquity\translation\MessagesDomain;
use Ubiquity\translation\TranslatorManager;

abstract class AbstractImportation {

	protected $file;

	public function __construct($file) {
		$this->file = $file;
	}

	/**
	 * return array
	 */
	public abstract function load();

	public function import($locale, $domain) {
		$msgDomain = new MessagesDomain($locale, TranslatorManager::getLoader(), $domain);
		$msgDomain->load();
		$messages = $msgDomain->getMessages();
		$messagesUpdates = new MessagesUpdates($locale, $domain);
		$messagesUpdates->load();
		$messages = $messagesUpdates->mergeMessages($messages);
		$newMessages = $this->load();
		foreach ($newMessages as $k => $msg) {
			if (! isset($messages[$k])) {
				$messagesUpdates->addValue($k, $msg, uniqid('key'));
			} else {
				$messagesUpdates->updateValue($k, $msg);
			}
		}
		$messagesUpdates->save();
	}
}

