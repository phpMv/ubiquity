<?php

namespace Ubiquity\translation;

use Ubiquity\translation\loader\LoaderInterface;

/**
 * Catalog of translation messages associated to a locale.
 * Ubiquity\translation$MessagesCatalog
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
class MessagesCatalog {
	protected $messagesDomains;
	/**
	 *
	 * @var LoaderInterface
	 */
	protected $loader;
	protected $locale;

	public function __construct($locale, LoaderInterface $loader) {
		$this->locale = $locale;
		$this->loader = $loader;
		$this->messagesDomains = [ ];
	}

	public function load() {
		$this->messagesDomains = [ ];
		$domains = $this->getDomains ();
		foreach ( $domains as $domain ) {
			$do = new MessagesDomain ( $this->locale, $this->loader, $domain );
			$do->load ();
			$this->messagesDomains [] = $do;
		}
	}

	public function getDomains() {
		return $this->loader->getDomains ( $this->locale );
	}

	/**
	 *
	 * @return MessagesDomain[]
	 */
	public function getMessagesDomains() {
		return $this->messagesDomains;
	}
}

