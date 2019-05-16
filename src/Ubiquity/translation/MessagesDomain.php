<?php

namespace Ubiquity\translation;

use Ubiquity\translation\loader\LoaderInterface;

/**
 * Represents a list of messages in a domain for a locale.
 * Ubiquity\translation$MessagesDomain
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
class MessagesDomain {
	protected $messages;
	protected $loader;
	protected $locale;
	protected $domain;

	public function __construct($locale=null, LoaderInterface $loader=null, $domain=null) {
		$this->locale = $locale;
		$this->loader = $loader;
		$this->domain = $domain;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getMessages() {
		return $this->messages;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getLoader() {
		return $this->loader;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getLocale() {
		return $this->locale;
	}

	/**
	 *
	 * @param mixed $messages
	 */
	public function setMessages($messages) {
		$this->messages = $messages;
	}

	/**
	 *
	 * @param mixed $loader
	 */
	public function setLoader($loader) {
		$this->loader = $loader;
	}

	/**
	 *
	 * @param mixed $locale
	 */
	public function setLocale($locale) {
		$this->locale = $locale;
	}

	public function store() {
		$this->loader->save ( $this->messages, $this->locale, $this->domain );
	}

	public function load() {
		$this->messages = $this->loader->loadDomain ( $this->locale, $this->domain );
	}
	/**
	 * @return string
	 */
	public function getDomain() {
		return $this->domain;
	}

}

