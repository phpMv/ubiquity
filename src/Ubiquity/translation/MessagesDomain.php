<?php

namespace Ubiquity\translation;

use Ubiquity\translation\loader\LoaderInterface;

class MessagesDomain {
	protected $messages;
	protected $loader;
	protected $locale;
	
	public function __construct($locale,LoaderInterface $loader){
		$this->locale=$locale;
		$this->loader=$loader;
	}
	/**
	 * @return mixed
	 */
	public function getMessages() {
		return $this->messages;
	}

	/**
	 * @return mixed
	 */
	public function getLoader() {
		return $this->loader;
	}

	/**
	 * @return mixed
	 */
	public function getLocale() {
		return $this->locale;
	}

	/**
	 * @param mixed $messages
	 */
	public function setMessages($messages) {
		$this->messages = $messages;
	}

	/**
	 * @param mixed $loader
	 */
	public function setLoader($loader) {
		$this->loader = $loader;
	}

	/**
	 * @param mixed $locale
	 */
	public function setLocale($locale) {
		$this->locale = $locale;
	}
	
	public function store($domain){
		$this->loader->save($this->messages, $this->locale, $domain);
	}

}

