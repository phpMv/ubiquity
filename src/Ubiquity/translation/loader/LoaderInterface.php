<?php

namespace Ubiquity\translation\loader;

/**
 * Translations loader interface.
 * Ubiquity\translation\loader$LoaderInterface
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
interface LoaderInterface {

	public function load($locale, $domain = 'messages');

	public function save($messages, $locale, $domain);

	public function clearCache($locale = null, $domain = null);

	public function loadDomain($locale, $domain);

	public function getDomains($locale);

	public function cacheExists($locale, $domain = '*');
}

